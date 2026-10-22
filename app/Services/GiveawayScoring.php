<?php

namespace App\Services;

use App\Models\GiveawayCampaign;
use App\Models\GiveawayEntry;
use Illuminate\Support\Facades\DB;

/**
 * Referral score of a giveaway entry (PRD v1.2 G1).
 *
 * score = number of VALID referral signups (referral_signups.status = 'valid', i.e. the referred customer
 * was engaged and passed the daily validation) brought in through the entrant's referral links of the
 * campaign's merchant, where the signup was created inside the campaign window [starts_at, ends_at].
 * A signup that is still pending when a draw is made does not count: validation takes a few days.
 *
 * entry_count (the number the leaderboards already show) = 1 + score * entries_per_referral. It is only
 * rewritten for campaigns that carry T&C (i.e. created after G1); older campaigns keep their stored counts.
 */
class GiveawayScoring
{
    /**
     * Recompute scores (and entry counts) of a campaign. Returns the number of entries that changed.
     *
     * @param int[]|null $entryIds limit to these entries (null = all)
     */
    public function refresh(GiveawayCampaign $campaign, ?array $entryIds = null): int
    {
        $query = GiveawayEntry::where('giveaway_campaign_id', $campaign->id);
        if ($entryIds !== null) {
            $query->whereIn('id', $entryIds);
        }
        $entries = $query->get(['id', 'customer_id', 'score', 'entry_count']);
        if ($entries->isEmpty()) {
            return 0;
        }

        $from = $campaign->starts_at ?? $campaign->created_at;
        $to = $campaign->ends_at ?? now();

        $scores = DB::table('referral_signups as s')
            ->join('referrals as r', 'r.id', '=', 's.referral_id')
            ->where('r.merchant_id', $campaign->merchant_id)
            ->where('s.status', 'valid')
            ->where('s.created_at', '>=', $from)
            ->where('s.created_at', '<=', $to)
            ->whereIn('r.customer_id', $entries->pluck('customer_id'))
            ->groupBy('r.customer_id')
            ->selectRaw('r.customer_id as customer_id, COUNT(*) as n')
            ->pluck('n', 'customer_id');

        $rewriteCount = filled($campaign->terms);
        $perReferral = max(1, (int) $campaign->entries_per_referral);
        $changed = 0;

        foreach ($entries as $entry) {
            $score = (int) ($scores[$entry->customer_id] ?? 0);
            $update = [];
            if ((int) $entry->score !== $score) {
                $update['score'] = $score;
            }
            if ($rewriteCount && (int) $entry->entry_count !== 1 + $score * $perReferral) {
                $update['entry_count'] = 1 + $score * $perReferral;
            }
            if ($update) {
                $update['updated_at'] = now();
                GiveawayEntry::where('id', $entry->id)->update($update);
                $changed++;
            }
        }

        return $changed;
    }
}
