<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerMerchant;
use App\Models\LoyaltyRate;
use App\Models\PointsTransaction;
use App\Models\Referral;
use App\Models\ReferralClick;
use App\Models\ReferralSignup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Customer referral attribution (PRD v1.2 5.3.6).
 *
 *  1. attribute()       - called at registration; links the new customer to the referral whose
 *                         /ref/{code} link they came through (the code lives in the session).
 *  2. validatePending() - run daily; a signup becomes VALID after N days if the new customer is
 *                         engaged and not flagged, then both sides get the merchant-set bonus points.
 */
class ReferralAttribution
{
    /**
     * Record that $customer registered through a referral link. Never throws to the caller's
     * transaction: all writes run in a savepoint that is rolled back on error.
     */
    public function attribute(Customer $customer, Request $request): ?ReferralSignup
    {
        $code = session('referral_code');
        if (!$code) {
            return null;
        }

        try {
            $signup = DB::transaction(function () use ($customer, $request, $code) {
                $referral = Referral::active()->where('referral_code', $code)->first();
                if (!$referral) {
                    return null;
                }

                $referrer = Customer::find($referral->customer_id);
                // no self-referral (same account or same e-mail)
                if (!$referrer || $referrer->id === $customer->id || strcasecmp((string) $referrer->email, (string) $customer->email) === 0) {
                    return null;
                }

                // already attributed (e.g. double submit)
                if (ReferralSignup::where('referred_customer_id', $customer->id)->exists()) {
                    return null;
                }

                // Fraud flags. The client IP is real since Cloudflare's ranges are trusted (bootstrap/app.php); only a
                // salted hash of it is stored. Phone/e-mail are unique per customer, so they need no flag. Flagged rows are
                // never auto-credited: the merchant decides on the Referral Review page.
                $ip = (string) $request->ip();
                $ipHash = $ip !== '' ? hash('sha256', config('app.key') . '|' . $ip) : null;
                $flags = [];
                if ($ipHash && ReferralSignup::where('referral_id', $referral->id)->where('signup_ip_hash', $ipHash)
                        ->where('created_at', '>=', now()->subDays(7))->count() >= (int) config('referrals.max_per_ip', 2)) {
                    $flags[] = 'duplicate_ip';
                }
                if (ReferralSignup::where('referral_id', $referral->id)->where('created_at', '>=', now()->subHour())->count() >= (int) config('referrals.max_per_hour', 5)) {
                    $flags[] = 'velocity';
                }

                // the referred customer becomes a customer of the merchant who owns the link
                $tied = CustomerMerchant::where('customer_id', $customer->id)
                    ->where('merchant_id', $referral->merchant_id)->exists();
                if (!$tied) {
                    CustomerMerchant::create([
                        'customer_id'       => $customer->id,
                        'merchant_id'       => $referral->merchant_id,
                        'points'            => 0,
                        'tier_per_merchant' => 'Basic',
                        'tied_at'           => now(),
                    ]);
                }

                $signup = ReferralSignup::create([
                    'referral_id'          => $referral->id,
                    'referred_customer_id' => $customer->id,
                    'status'               => 'pending',
                    'signup_ip_hash'       => $ipHash,
                    'flags'                => $flags ?: null,
                ]);

                DB::table('customers')->where('id', $customer->id)->update([
                    'referred_by' => $referrer->id,
                    'referral_id' => $referral->id,
                ]);

                $referral->trackSignup();

                // Mark the most recent unconverted click on this link as converted. Query-builder update on
                // purpose: `converted` is not in ReferralClick::$fillable, so $model->update() would silently
                // ignore it. (Clicks are not matched by IP: see the note above.)
                $clickId = ReferralClick::where('referral_id', $referral->id)->where('converted', false)->max('id');
                if ($clickId) {
                    ReferralClick::where('id', $clickId)->update(['converted' => true]);
                }

                return $signup;
            });
        } catch (\Throwable $e) {
            Log::warning('Referral attribution failed: ' . $e->getMessage());
            $signup = null;
        }

        session()->forget(['referral_code', 'referral_merchant_id']);

        return $signup;
    }

    /**
     * Promote (or retire) pending signups. Idempotent: each signup is claimed with a conditional
     * UPDATE so overlapping runs can never pay a bonus twice.
     *
     * @return array{validated:int, invalidated:int, held:int, flagged:int}
     */
    public function validatePending(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $stats = ['validated' => 0, 'invalidated' => 0, 'held' => 0, 'flagged' => 0];
        $minDays = (int) config('referrals.min_days', 3);

        ReferralSignup::where('status', 'pending')
            ->where('created_at', '<=', $now->copy()->subDays($minDays))
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (&$stats, $now) {
                foreach ($rows as $signup) {
                    try {
                        $result = $this->process($signup, $now);
                    } catch (\Throwable $e) {
                        Log::error("Referral validation failed for signup {$signup->id}: " . $e->getMessage());
                        $result = 'held';
                    }
                    $stats[$result]++;
                }
            });

        return $stats;
    }

    private function process(ReferralSignup $signup, Carbon $now): string
    {
        $referral = Referral::find($signup->referral_id);
        $customer = Customer::find($signup->referred_customer_id);
        if (!$referral || !$customer) {
            return $this->invalidate($signup, 'missing_record');
        }

        // flagged signups wait for a merchant review (UI planned, PRD 6.3.4); never auto-credited
        if (!empty($signup->flags)) {
            return 'flagged';
        }

        $merchantId = (int) $referral->merchant_id;

        if (!$this->isEngaged($customer, $merchantId)) {
            $tooOld = $signup->created_at->lte($now->copy()->subDays((int) config('referrals.max_days', 30)));
            return $tooOld ? $this->invalidate($signup, 'not_active') : 'held';
        }

        // monthly cap per referrer (all of that customer's referral links)
        $cap = (int) config('referrals.monthly_cap', 20);
        $referralIds = Referral::where('customer_id', $referral->customer_id)->pluck('id');
        $validThisMonth = ReferralSignup::whereIn('referral_id', $referralIds)->where('status', 'valid')
            ->whereBetween('validated_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();
        if ($validThisMonth >= $cap) {
            return 'held';
        }

        // the referrer must still be a member of this merchant to receive points
        $referrerTied = CustomerMerchant::where('customer_id', $referral->customer_id)
            ->where('merchant_id', $merchantId)->exists();
        if (!$referrerTied) {
            return $this->invalidate($signup, 'referrer_not_member');
        }

        $rate = LoyaltyRate::where('merchant_id', $merchantId)->first();
        $pointsReferrer = (int) ($rate->referral_bonus_referrer ?? 0);
        $pointsReferred = (int) ($rate->referral_bonus_referred ?? 0);

        return DB::transaction(function () use ($signup, $referral, $merchantId, $pointsReferrer, $pointsReferred, $now) {
            // claim: only one runner can flip pending -> valid
            $claimed = ReferralSignup::where('id', $signup->id)->where('status', 'pending')->update([
                'status'          => 'valid',
                'validated_at'    => $now,
                'points_referrer' => $pointsReferrer,
                'points_referred' => $pointsReferred,
                'updated_at'      => $now,
            ]);
            if (!$claimed) {
                return 'held';
            }

            if ($pointsReferrer > 0) {
                $this->credit($referral->customer_id, $merchantId, $pointsReferrer, 'Referral bonus (referrer)');
            }
            if ($pointsReferred > 0) {
                $this->credit($signup->referred_customer_id, $merchantId, $pointsReferred, 'Referral bonus (welcome)');
            }
            $referral->trackConversion($pointsReferrer);

            return 'validated';
        });
    }

    /**
     * Engaged = at least one approved earn (with this merchant) or one approved task submission.
     * Both require a merchant/staff decision, so they cannot be faked by one click. A giveaway
     * entry is deliberately NOT enough (it is a single click).
     */
    private function isEngaged(Customer $customer, int $merchantId): bool
    {
        $earned = PointsTransaction::where('customer_id', $customer->id)
            ->where('merchant_id', $merchantId)
            ->where('type', 'earn')->where('status', 'approved')
            ->where('notes', 'not like', 'Referral bonus%')
            ->exists();
        if ($earned) {
            return true;
        }

        return DB::table('task_submissions')->where('customer_id', $customer->id)->where('status', 'approved')->exists();
    }

    private function credit(int $customerId, int $merchantId, int $points, string $note): void
    {
        PointsTransaction::create([
            'customer_id' => $customerId,
            'merchant_id' => $merchantId,
            'type'        => 'earn',
            'points'      => $points,
            'status'      => 'approved',
            'notes'       => $note,
        ]);

        CustomerMerchant::where('customer_id', $customerId)
            ->where('merchant_id', $merchantId)
            ->increment('points', $points);
    }

    private function invalidate(ReferralSignup $signup, string $reason): string
    {
        ReferralSignup::where('id', $signup->id)->where('status', 'pending')
            ->update(['status' => 'invalid', 'invalid_reason' => $reason, 'updated_at' => now()]);

        return 'invalidated';
    }

}
