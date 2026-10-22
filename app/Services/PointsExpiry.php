<?php

namespace App\Services;

use App\Mail\PointsExpiryWarningMail;
use App\Models\Customer;
use App\Models\InAppNotification;
use App\Models\LoyaltyRate;
use App\Models\Merchant;
use App\Models\PointsTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Points expiry (PRD v1.2 4.2.2 / D13).
 *
 * A customer's points with a merchant lapse when there has been no ACTIVITY for N months. Activity = the latest approved
 * earn or redeem row (voids, expiries and pending rows do not count); a member without any transaction counts from the
 * day they joined. N = loyalty_rates.points_expiry_months, else config('points.default_months'); 0 = never.
 *
 * The lapse is written to the ledger as one approved 'expired' row for the whole balance (nothing is deleted or edited),
 * and the balance goes to 0. Customers are warned once, `warn_days` before the lapse (again only after new activity).
 */
class PointsExpiry
{
    /** @var array<int,int> merchant_id => months (0 = never) */
    private array $months = [];

    public function monthsFor(int $merchantId): int
    {
        if (!array_key_exists($merchantId, $this->months)) {
            $override = LoyaltyRate::where('merchant_id', $merchantId)->value('points_expiry_months');
            $this->months[$merchantId] = $override === null ? (int) config('points.default_months', 12) : (int) $override;
        }

        return $this->months[$merchantId];
    }

    public function lastActivity(int $customerId, int $merchantId, $joinedAt): Carbon
    {
        $last = PointsTransaction::where('customer_id', $customerId)->where('merchant_id', $merchantId)
            ->whereIn('type', ['earn', 'redeem'])->where('status', 'approved')->max('created_at');
        $joined = $joinedAt ? Carbon::parse($joinedAt) : now();

        return $last ? Carbon::parse($last)->max($joined) : $joined;
    }

    /** When this customer's points with the merchant would lapse if nothing happens; null = never (merchant setting 0). */
    public function lapseAt(int $customerId, int $merchantId, $joinedAt): ?Carbon
    {
        $months = $this->monthsFor($merchantId);
        if ($months <= 0) {
            return null;
        }

        return $this->lastActivity($customerId, $merchantId, $joinedAt)->copy()->addMonths($months);
    }

    /**
     * @return array{expired:int, points:float, warned:int}
     */
    public function run(bool $dryRun = false, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $out = ['expired' => 0, 'points' => 0.0, 'warned' => 0];
        $warnDays = (int) config('points.warn_days', 30);
        $this->months = [];

        DB::table('customer_merchant')->where('points', '>', 0)->orderBy('id')->chunkById(200, function ($rows) use (&$out, $now, $warnDays, $dryRun) {
            foreach ($rows as $cm) {
                $months = $this->monthsFor((int) $cm->merchant_id);
                if ($months <= 0) {
                    continue;
                }
                $last = $this->lastActivity((int) $cm->customer_id, (int) $cm->merchant_id, $cm->tied_at ?? $cm->created_at);
                $lapseAt = $last->copy()->addMonths($months);

                if ($now->gte($lapseAt)) {
                    $points = $dryRun ? (float) $cm->points : $this->expire($cm, $now);
                    if ($points > 0) {
                        $out['expired']++;
                        $out['points'] += $points;
                    }
                } elseif ($now->gte($lapseAt->copy()->subDays($warnDays))) {
                    $already = $cm->expiry_warned_at && Carbon::parse($cm->expiry_warned_at)->gte($last);
                    if (!$already) {
                        $out['warned']++;
                        if (!$dryRun) {
                            DB::table('customer_merchant')->where('id', $cm->id)->update(['expiry_warned_at' => $now]);
                            $this->notify((int) $cm->customer_id, (int) $cm->merchant_id,
                                '⏳ Mata anda akan tamat tempoh',
                                number_format((float) $cm->points) . ' mata anda di {merchant} akan tamat pada ' . $lapseAt->format('d/m/Y') . '. Buat transaksi atau tebus ganjaran sebelum itu untuk mengekalkannya.');
                            $this->emailWarning((int) $cm->customer_id, (int) $cm->merchant_id, (float) $cm->points, $lapseAt);
                        }
                    }
                }
            }
        });

        return $out;
    }

    /** Writes the 'expired' ledger row and zeroes the balance; returns the points removed (0 if nothing was left). */
    private function expire(object $cmRow, Carbon $now): float
    {
        return (float) DB::transaction(function () use ($cmRow, $now) {
            $cm = DB::table('customer_merchant')->where('id', $cmRow->id)->lockForUpdate()->first();
            if (!$cm || (float) $cm->points <= 0) {
                return 0;
            }
            $points = (float) $cm->points;
            $last = $this->lastActivity((int) $cm->customer_id, (int) $cm->merchant_id, $cm->tied_at ?? $cm->created_at);

            PointsTransaction::create([
                'customer_id' => $cm->customer_id,
                'merchant_id' => $cm->merchant_id,
                'type'        => 'expired',
                'points'      => -$points,
                'status'      => 'approved',
                'notes'       => 'Points expired: no activity since ' . $last->format('Y-m-d'),
            ]);
            DB::table('customer_merchant')->where('id', $cm->id)->update(['points' => 0, 'updated_at' => $now]);

            $this->notify((int) $cm->customer_id, (int) $cm->merchant_id, '⌛ Mata telah tamat tempoh',
                number_format($points) . ' mata anda di {merchant} telah tamat tempoh kerana tiada aktiviti sejak ' . $last->format('d/m/Y') . '.');

            return $points;
        });
    }

    /** Queued e-mail version of the warning. Never breaks the run. */
    private function emailWarning(int $customerId, int $merchantId, float $points, Carbon $lapseAt): void
    {
        try {
            $customer = Customer::where('id', $customerId)->first(['name', 'email']);
            if ($customer && $customer->email) {
                $company = Merchant::where('id', $merchantId)->value('company_name') ?? 'merchant';
                Mail::to($customer->email)->queue(new PointsExpiryWarningMail($customer->name, $company, (int) $points, $lapseAt->format('d/m/Y')));
            }
        } catch (\Throwable $e) {
            Log::warning('Points expiry warning e-mail failed: ' . $e->getMessage());
        }
    }

    private function notify(int $customerId, int $merchantId, string $title, string $message): void
    {
        try {
            $email = Customer::where('id', $customerId)->value('email');
            $userId = $email ? User::where('email', $email)->value('id') : null;
            if ($userId) {
                $company = Merchant::where('id', $merchantId)->value('company_name') ?? 'merchant';
                InAppNotification::create(['user_id' => $userId, 'title' => $title, 'message' => str_replace('{merchant}', $company, $message), 'type' => 'info']);
            }
        } catch (\Throwable $e) {
            Log::warning('Points expiry notification failed: ' . $e->getMessage());
        }
    }
}
