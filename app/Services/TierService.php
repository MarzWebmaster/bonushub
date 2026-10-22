<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerMerchant;
use App\Models\InAppNotification;
use App\Models\Merchant;
use App\Models\MerchantTier;
use App\Models\PointsTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Per-merchant membership tier (PRD v1.2 7.2 / D6): the tier follows the points EARNED in the last 12 months
 * (approved 'earn' rows, minus the ones that were voided; redemptions do not lower it).
 *
 *  refresh()         upgrade-only by default: a customer is never moved down by the daily job, and a tier name the
 *                    merchant has no threshold for (old seed value such as 'Bronze') is left alone.
 *  allowDowngrade    used when the merchant changes the thresholds: everyone is re-resolved in both directions.
 */
class TierService
{
    public function earnedRolling(int $customerId, int $merchantId, ?Carbon $now = null): float
    {
        $now = $now ?? now();

        return (float) PointsTransaction::where('customer_id', $customerId)
            ->where('merchant_id', $merchantId)
            ->where('type', 'earn')->where('status', 'approved')
            ->where('created_at', '>=', $now->copy()->subMonths(12))
            ->whereNotExists(function ($q) {
                $q->from('points_transactions as v')->whereColumn('v.void_of_id', 'points_transactions.id');
            })
            ->sum('points');
    }

    /**
     * @return array{from:?string,to:string}|null  null when nothing changed
     */
    public function refresh(int $customerId, int $merchantId, bool $allowDowngrade = false, ?Carbon $now = null): ?array
    {
        $tiers = MerchantTier::where('merchant_id', $merchantId)->orderBy('min_points')->get();
        if ($tiers->isEmpty()) {
            return null;
        }
        $cm = CustomerMerchant::where('customer_id', $customerId)->where('merchant_id', $merchantId)->first();
        if (!$cm) {
            return null;
        }

        $earned = $this->earnedRolling($customerId, $merchantId, $now);
        $target = $tiers->last(fn ($t) => $earned >= $t->min_points) ?? ($allowDowngrade ? $tiers->first() : null);
        if (!$target) {
            return null;
        }

        $current = $tiers->firstWhere('tier_name', $cm->tier_per_merchant);
        if (!$current && !$allowDowngrade) {
            return null;   // legacy/unknown tier name: do not touch it automatically
        }
        $currentMin = $current ? (int) $current->min_points : -1;

        $move = $allowDowngrade ? ($target->min_points != $currentMin || $target->tier_name !== $cm->tier_per_merchant)
                                : ($target->min_points > $currentMin);
        if (!$move) {
            return null;
        }

        $from = $cm->tier_per_merchant;
        DB::table('customer_merchant')->where('id', $cm->id)->update(['tier_per_merchant' => $target->tier_name, 'updated_at' => now()]);

        if ($target->min_points > $currentMin) {
            $this->notifyUpgrade($customerId, $merchantId, $target->tier_name);
        }

        return ['from' => $from, 'to' => $target->tier_name];
    }

    /** Re-resolve every member of a merchant. Returns the number of customers whose tier changed. */
    public function refreshMerchant(int $merchantId, bool $allowDowngrade = false, bool $dryRun = false): int
    {
        $changed = 0;
        CustomerMerchant::where('merchant_id', $merchantId)->orderBy('id')->chunkById(200, function ($rows) use (&$changed, $allowDowngrade, $dryRun, $merchantId) {
            foreach ($rows as $cm) {
                if ($dryRun) {
                    $changed += $this->wouldChange($cm, $allowDowngrade) ? 1 : 0;
                } elseif ($this->refresh((int) $cm->customer_id, $merchantId, $allowDowngrade)) {
                    $changed++;
                }
            }
        });

        return $changed;
    }

    public function refreshAll(bool $dryRun = false): int
    {
        $n = 0;
        Merchant::whereIn('id', MerchantTier::distinct()->pluck('merchant_id'))->each(function ($m) use (&$n, $dryRun) {
            $n += $this->refreshMerchant((int) $m->id, false, $dryRun);
        });

        return $n;
    }

    private function wouldChange(CustomerMerchant $cm, bool $allowDowngrade): bool
    {
        $tiers = MerchantTier::where('merchant_id', $cm->merchant_id)->orderBy('min_points')->get();
        $earned = $this->earnedRolling((int) $cm->customer_id, (int) $cm->merchant_id);
        $target = $tiers->last(fn ($t) => $earned >= $t->min_points);
        $current = $tiers->firstWhere('tier_name', $cm->tier_per_merchant);

        return $target && $current && $target->min_points > $current->min_points;
    }

    private function notifyUpgrade(int $customerId, int $merchantId, string $tier): void
    {
        try {
            $email = Customer::where('id', $customerId)->value('email');
            $userId = $email ? User::where('email', $email)->value('id') : null;
            if ($userId) {
                $company = Merchant::where('id', $merchantId)->value('company_name') ?? 'merchant';
                InAppNotification::create([
                    'user_id' => $userId,
                    'title'   => "🎖️ Naik tier: {$tier}",
                    'message' => "Tahniah! Anda kini tier {$tier} di {$company}. Terima kasih kerana terus menyokong.",
                    'type'    => 'info',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Tier upgrade notification failed: ' . $e->getMessage());
        }
    }
}
