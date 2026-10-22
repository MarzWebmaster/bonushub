<?php

namespace App\Http\Controllers;

use App\Mail\GiveawayWinnerMail;
use App\Models\CustomerMerchant;
use App\Models\GiveawayCampaign;
use App\Models\GiveawayEntry;
use App\Models\GiveawayWinner;
use App\Models\InAppNotification;
use App\Models\Merchant;
use App\Models\Referral;
use App\Models\User;
use App\Services\GiveawayScoring;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class GiveawayController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function getMerchant(): Merchant
    {
        $user = Auth::user();
        return $user->merchant ?? Merchant::where('user_id', $user->id)->firstOrFail();
    }

    // ========================
    // MERCHANT — Campaign Management
    // ========================

    public function merchantCampaignsPage(): View
    {
        $merchant = $this->getMerchant();
        $campaigns = GiveawayCampaign::where('merchant_id', $merchant->id)
            ->withCount('entries')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_campaigns' => GiveawayCampaign::where('merchant_id', $merchant->id)->count(),
            'active_campaigns' => GiveawayCampaign::where('merchant_id', $merchant->id)->where('status', 'active')->count(),
            'total_entries' => DB::table('giveaway_entries')
                ->whereIn('giveaway_campaign_id', GiveawayCampaign::where('merchant_id', $merchant->id)->pluck('id'))
                ->sum('entry_count'),
            'total_participants' => DB::table('giveaway_entries')
                ->whereIn('giveaway_campaign_id', GiveawayCampaign::where('merchant_id', $merchant->id)->pluck('id'))
                ->distinct('customer_id')
                ->count('customer_id'),
        ];

        return view('merchant.giveaways.index', compact('campaigns', 'stats'));
    }

    public function merchantCreatePage(): View
    {
        return view('merchant.giveaways.create');
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $endsRules = ['required', 'date', 'after:now'];
        if ($request->filled('starts_at')) {
            $endsRules[] = 'after:starts_at';
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'prize_description' => 'required|string|max:300',
            'prize_value' => 'nullable|integer|min:0',
            'winner_count' => 'required|integer|min:1|max:100',
            // 'random' stays out on purpose (PRD v1.2 D1: merit-based contest, no random draw)
            'selection_method' => 'required|in:manual,top_referrers,threshold,milestone',
            'entry_method' => 'required|in:referral,task,purchase,manual',
            'entries_per_referral' => 'nullable|integer|min:1|max:10',
            'max_entries' => 'nullable|integer|min:10',
            'starts_at' => 'nullable|date',
            'ends_at' => $endsRules,
            'terms' => 'required|string|min:20|max:5000',
            'claim_days' => 'nullable|integer|min:1|max:60',
            'threshold_score' => 'required_if:selection_method,threshold|nullable|integer|min:1|max:100000',
            'milestones' => 'required_if:selection_method,milestone|nullable|array|min:1|max:10',
            'milestones.*.score' => 'required|integer|min:1|max:100000|distinct',
            'milestones.*.prize' => 'required|string|max:200',
        ]);

        // keep only the settings that belong to the chosen method
        if ($validated['selection_method'] !== 'threshold') {
            $validated['threshold_score'] = null;
        }
        if ($validated['selection_method'] === 'milestone') {
            $validated['milestones'] = collect($validated['milestones'])
                ->map(fn ($m) => ['score' => (int) $m['score'], 'prize' => trim($m['prize'])])
                ->sortBy('score')->values()->all();
        } else {
            $validated['milestones'] = null;
        }
        $validated['entries_per_referral'] = $validated['entries_per_referral'] ?? 1;
        $validated['prize_value'] = $validated['prize_value'] ?? 0;
        $validated['claim_days'] = $validated['claim_days'] ?? 7;

        $merchant = $this->getMerchant();
        if ($merchant->limit('giveaway_limit') === 0) {
            return response()->json(['success' => false, 'message' => 'Pakej anda tidak termasuk giveaway. Naik taraf pakej untuk menggunakannya.'], 422);
        }
        $campaign = GiveawayCampaign::create([
            ...$validated,
            'merchant_id' => $merchant->id,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Giveaway campaign created!',
            'campaign' => $campaign,
        ], 201);
    }

    public function merchantCampaignDetail(int $campaignId): View
    {
        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)
            ->withCount('entries')
            ->with(['entries.customer', 'winners.customer'])
            ->findOrFail($campaignId);

        $leaderboard = GiveawayEntry::where('giveaway_campaign_id', $campaignId)
            ->with('customer')
            ->ranked()
            ->limit(20)
            ->get();

        return view('merchant.giveaways.detail', compact('campaign', 'leaderboard'));
    }

    public function activateCampaign(int $campaignId): JsonResponse
    {
        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)->findOrFail($campaignId);

        if ($campaign->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft campaigns can be activated.'], 422);
        }
        if (!filled($campaign->terms) || mb_strlen(trim($campaign->terms)) < 20) {
            return response()->json(['success' => false, 'message' => 'Terma & syarat diperlukan sebelum giveaway boleh diaktifkan.'], 422);
        }
        if (!$campaign->ends_at || $campaign->ends_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'Tarikh tamat giveaway mesti pada masa depan.'], 422);
        }

        $limit = $merchant->limit('giveaway_limit');
        if ($limit >= 0 && GiveawayCampaign::where('merchant_id', $merchant->id)->where('status', 'active')->count() >= $limit) {
            return response()->json(['success' => false, 'message' => "Had giveaway aktif pakej anda ({$limit}) telah dicapai. Tamatkan satu atau naik taraf pakej."], 422);
        }

        $campaign->update([
            'status' => 'active',
            'starts_at' => $campaign->starts_at ?? now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Campaign activated! 🚀']);
    }

    public function endCampaign(int $campaignId): JsonResponse
    {
        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)->findOrFail($campaignId);

        if ($campaign->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Only active campaigns can be ended.'], 422);
        }

        $campaign->update(['status' => 'ended']);
        return response()->json(['success' => true, 'message' => 'Campaign ended.']);
    }

    /**
     * Rank the entrants who could win under the campaign's rule, best first, skipping customers already named.
     * Used by the first draw and by replacements. Manual campaigns have no automatic ranking.
     *
     *  top_referrers : score >= 1
     *  threshold     : score >= threshold_score
     *  milestone     : score >= lowest milestone, prize = highest milestone reached
     * Ties go to the earlier entry. Returns [[GiveawayEntry, prize, note], ...].
     */
    private function rankedCandidates(GiveawayCampaign $c, string $method, array $excludeCustomerIds, int $limit): array
    {
        $base = GiveawayEntry::where('giveaway_campaign_id', $c->id);
        if ($excludeCustomerIds) {
            $base->whereNotIn('customer_id', $excludeCustomerIds);
        }
        $picked = [];

        switch ($method) {
            case 'top_referrers':
                foreach ((clone $base)->where('score', '>=', 1)->ranked()->take($limit)->get() as $e) {
                    $picked[] = [$e, $c->prize_description, "Top referrer: score {$e->score}"];
                }
                break;

            case 'threshold':
                $min = (int) $c->threshold_score;
                if ($min < 1) {
                    throw new \DomainException('Skor sasaran belum ditetapkan.');
                }
                foreach ((clone $base)->where('score', '>=', $min)->ranked()->take($limit)->get() as $e) {
                    $picked[] = [$e, $c->prize_description, "Reached target {$min}: score {$e->score}"];
                }
                break;

            case 'milestone':
                $ladder = $c->milestoneLadder();
                if (!$ladder) {
                    throw new \DomainException('Milestone belum ditetapkan.');
                }
                $lowest = end($ladder)['score'];
                foreach ((clone $base)->where('score', '>=', $lowest)->ranked()->take($limit)->get() as $e) {
                    $picked[] = [$e, $c->milestonePrizeFor((int) $e->score), "Milestone reached with score {$e->score}"];
                }
                break;
        }

        return $picked;
    }

    private function claimDeadline(GiveawayCampaign $c): \Illuminate\Support\Carbon
    {
        return now()->addDays(max(1, (int) ($c->claim_days ?: 7)));
    }

    /**
     * Draw the winners. The method is fixed per campaign (selection_method); the posted `method` is only
     * accepted when it matches. Allowed once the campaign was ended or its end time has passed. Each winner gets a
     * claim deadline (campaign.claim_days, default 7) and an in-app message + e-mail.
     *
     *  top_referrers / threshold / milestone : see rankedCandidates(), capped at winner_count
     *  manual        : the merchant names entrants and must record a reason (kept in giveaway_winners.notes)
     * Scores are refreshed inside the transaction first.
     */
    public function selectWinners(Request $request, int $campaignId): JsonResponse
    {
        $validated = $request->validate([
            'method' => 'nullable|in:manual,top_referrers,threshold,milestone',
            'winner_ids' => 'nullable|array|max:100',
            'winner_ids.*' => 'integer',
            'reason' => 'nullable|string|max:500',
        ]);

        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)->findOrFail($campaignId);

        $method = $campaign->selection_method;
        if (!in_array($method, ['manual', 'top_referrers', 'threshold', 'milestone'], true)) {
            return response()->json(['success' => false, 'message' => 'Kaedah pemilihan kempen ini tidak disokong.'], 422);
        }
        if (!empty($validated['method']) && $validated['method'] !== $method) {
            return response()->json(['success' => false, 'message' => "Kempen ini menggunakan kaedah '{$method}'."], 422);
        }
        if ($method === 'manual') {
            if (empty($validated['winner_ids'])) {
                return response()->json(['success' => false, 'message' => 'Pilih sekurang-kurangnya seorang pemenang.'], 422);
            }
            if (mb_strlen(trim((string) ($validated['reason'] ?? ''))) < 10) {
                return response()->json(['success' => false, 'message' => 'Sebab pemilihan manual diperlukan (sekurang-kurangnya 10 aksara).'], 422);
            }
        }

        $winners = [];
        try {
            DB::transaction(function () use ($campaign, $method, $validated, &$winners) {
                // serialise concurrent draws of the same campaign
                $locked = GiveawayCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
                if (!$locked->readyForDraw()) {
                    throw new \DomainException('Tamatkan giveaway (atau tunggu tarikh tamat) sebelum memilih pemenang.');
                }
                if (GiveawayWinner::where('giveaway_campaign_id', $locked->id)->exists()) {
                    throw new \DomainException('Winners already selected.');
                }

                app(GiveawayScoring::class)->refresh($locked);
                $picked = []; // [entry, prize, note]

                if ($method === 'manual') {
                    $reason = trim($validated['reason']);
                    foreach (array_values(array_unique($validated['winner_ids'])) as $customerId) {
                        $e = GiveawayEntry::where('giveaway_campaign_id', $locked->id)->where('customer_id', $customerId)->first();
                        if (!$e) {
                            throw new \DomainException("Pelanggan #{$customerId} tiada dalam senarai penyertaan.");
                        }
                        $picked[] = [$e, $locked->prize_description, "Manual: {$reason}"];
                    }
                    if (count($picked) > $locked->winner_count) {
                        throw new \DomainException("Maksimum {$locked->winner_count} pemenang.");
                    }
                } else {
                    $picked = $this->rankedCandidates($locked, $method, [], (int) $locked->winner_count);
                }

                if (!$picked) {
                    throw new \DomainException('Tiada peserta yang layak menang mengikut peraturan kempen.');
                }

                $deadline = $this->claimDeadline($locked);
                foreach ($picked as $i => [$entry, $prize, $note]) {
                    $w = GiveawayWinner::create([
                        'giveaway_campaign_id' => $locked->id,
                        'customer_id' => $entry->customer_id,
                        'giveaway_entry_id' => $entry->id,
                        'position' => $i + 1,
                        'prize_description' => $prize,
                        'notes' => $note,
                        'claim_deadline_at' => $deadline,
                    ]);
                    $entry->update(['is_winner' => true, 'prize_won' => $prize, 'won_at' => now()]);
                    $winners[] = $w;
                }

                $locked->update(['status' => 'ended', 'winners_announced_at' => now()]);
            });
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Winner selection failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to select winners.'], 500);
        }

        Log::info("Giveaway winners selected for campaign #{$campaign->id}", ['method' => $method, 'winner_count' => count($winners)]);
        foreach ($winners as $w) {
            $this->notifyWinner($campaign, $w, false);
        }

        return response()->json([
            'success' => true,
            'message' => count($winners) . ' winner(s) selected! 🎉',
            'winners' => GiveawayWinner::whereIn('id', collect($winners)->pluck('id'))->with('customer')->orderBy('position')->get(),
        ]);
    }

    /** The merchant confirms that the prize was handed over. */
    public function claimWinner(int $campaignId, int $winnerId): JsonResponse
    {
        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)->findOrFail($campaignId);
        $w = GiveawayWinner::where('giveaway_campaign_id', $campaign->id)->findOrFail($winnerId);

        $n = GiveawayWinner::where('id', $w->id)->where('status', 'pending')
            ->update(['status' => 'claimed', 'claimed_at' => now(), 'updated_at' => now()]);
        if (!$n) {
            return response()->json(['success' => false, 'message' => 'Hanya pemenang berstatus pending boleh ditanda sebagai telah menuntut.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Hadiah ditanda sebagai telah diserahkan.']);
    }

    /**
     * The winner did not claim in time (or cannot): forfeit them and name the next one under the same rule.
     * Automatic methods take the next ranked entrant who is not already a winner; for a manual campaign the merchant
     * names the replacement (`customer_id`) and records a reason. The replacement takes the same position and prize
     * rule, gets a fresh claim deadline and an in-app message + e-mail. A claimed winner cannot be replaced.
     */
    public function replaceWinner(Request $request, int $campaignId, int $winnerId): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|integer',
            'reason' => 'nullable|string|max:500',
        ]);

        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)->findOrFail($campaignId);
        GiveawayWinner::where('giveaway_campaign_id', $campaign->id)->findOrFail($winnerId);

        $new = null;
        try {
            DB::transaction(function () use ($campaign, $winnerId, $validated, &$new) {
                $locked = GiveawayCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
                $old = GiveawayWinner::where('id', $winnerId)->where('giveaway_campaign_id', $locked->id)->lockForUpdate()->firstOrFail();
                if ($old->status !== 'pending') {
                    throw new \DomainException('Hanya pemenang yang belum menuntut boleh diganti.');
                }

                app(GiveawayScoring::class)->refresh($locked);
                $already = GiveawayWinner::where('giveaway_campaign_id', $locked->id)->pluck('customer_id')->all();

                if ($locked->selection_method === 'manual') {
                    $reason = trim((string) ($validated['reason'] ?? ''));
                    if (empty($validated['customer_id']) || mb_strlen($reason) < 10) {
                        throw new \DomainException('Pilih pelanggan ganti dan tulis sebab (sekurang-kurangnya 10 aksara).');
                    }
                    if (in_array((int) $validated['customer_id'], array_map('intval', $already), true)) {
                        throw new \DomainException('Pelanggan ini sudah pernah dipilih sebagai pemenang.');
                    }
                    $entry = GiveawayEntry::where('giveaway_campaign_id', $locked->id)->where('customer_id', $validated['customer_id'])->first();
                    if (!$entry) {
                        throw new \DomainException('Pelanggan ini tiada dalam senarai penyertaan.');
                    }
                    $pick = [$entry, $locked->prize_description, "Manual replacement: {$reason}"];
                } else {
                    $next = $this->rankedCandidates($locked, $locked->selection_method, $already, 1);
                    if (!$next) {
                        throw new \DomainException('Tiada calon ganti yang layak mengikut peraturan kempen.');
                    }
                    $pick = $next[0];
                }
                [$entry, $prize, $note] = $pick;

                $old->update([
                    'status' => 'forfeited',
                    'notes' => trim($old->notes . ' | Forfeited ' . now()->format('Y-m-d') . ': replaced by customer #' . $entry->customer_id),
                ]);
                GiveawayEntry::where('id', $old->giveaway_entry_id)->update(['is_winner' => false, 'prize_won' => null, 'won_at' => null]);

                $new = GiveawayWinner::create([
                    'giveaway_campaign_id' => $locked->id,
                    'customer_id' => $entry->customer_id,
                    'giveaway_entry_id' => $entry->id,
                    'position' => $old->position,
                    'prize_description' => $prize,
                    'notes' => $note,
                    'claim_deadline_at' => $this->claimDeadline($locked),
                    'replaces_winner_id' => $old->id,
                ]);
                $entry->update(['is_winner' => true, 'prize_won' => $prize, 'won_at' => now()]);
            });
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Winner replacement failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menukar pemenang.'], 500);
        }

        $this->notifyWinner($campaign, $new, true);

        return response()->json(['success' => true, 'message' => 'Pemenang diganti.', 'winner' => $new->load('customer')]);
    }

    /** In-app message + queued e-mail to a winner (their user account is linked by e-mail). Never breaks the draw. */
    private function notifyWinner(GiveawayCampaign $campaign, GiveawayWinner $w, bool $replacement): void
    {
        try {
            $customer = DB::table('customers')->where('id', $w->customer_id)->first(['name', 'email']);
            if (!$customer) {
                return;
            }
            $deadline = $w->claim_deadline_at ? \Illuminate\Support\Carbon::parse($w->claim_deadline_at)->format('d/m/Y') : null;
            $userId = $customer->email ? User::where('email', $customer->email)->value('id') : null;
            if ($userId) {
                InAppNotification::create([
                    'user_id' => $userId,
                    'title' => '🎉 Anda menang giveaway!',
                    'message' => "Tahniah! Anda memenangi \"{$w->prize_description}\" dalam {$campaign->title}."
                        . ($deadline ? " Sila tuntut sebelum {$deadline}." : '') . ' Merchant akan menghubungi anda.',
                    'type' => 'info',
                ]);
            }
            if ($customer->email) {
                $merchantName = Merchant::where('id', $campaign->merchant_id)->value('company_name') ?? 'merchant';
                Mail::to($customer->email)->queue(new GiveawayWinnerMail($customer->name, $w->prize_description, $campaign->title, $merchantName, $deadline, $replacement));
                GiveawayWinner::where('id', $w->id)->update(['notified_at' => now()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Winner notification failed: ' . $e->getMessage());
        }
    }

    // ========================
    // CUSTOMER — Giveaway Participation
    // ========================

    public function customerGiveawaysPage(): View
    {
        return view('customer.giveaways');
    }

    public function giveawayDetail(int $campaignId): View
    {
        // drafts and cancelled campaigns are not public
        $campaign = GiveawayCampaign::whereIn('status', ['active', 'ended'])
            ->withCount('entries')
            ->with(['winners.customer', 'merchant'])
            ->findOrFail($campaignId);

        $userCustomer = Auth::user()->customer;
        $myEntry = null;
        if ($userCustomer) {
            $myEntry = GiveawayEntry::where('giveaway_campaign_id', $campaignId)
                ->where('customer_id', $userCustomer->id)
                ->first();
        }

        $leaderboard = GiveawayEntry::where('giveaway_campaign_id', $campaignId)
            ->with('customer')
            ->ranked()
            ->limit(20)
            ->get();

        // share box: the entrant's own referral link for this merchant (created on first view, never for non-entrants)
        $shareUrl = null;
        if ($myEntry && $userCustomer && $campaign->status === 'active') {
            $ref = Referral::where('customer_id', $userCustomer->id)->where('merchant_id', $campaign->merchant_id)->first()
                ?? Referral::create([
                    'referral_code' => Referral::generateCode($userCustomer->id, $campaign->merchant_id),
                    'customer_id' => $userCustomer->id,
                    'merchant_id' => $campaign->merchant_id,
                    'source' => 'giveaway',
                    'status' => 'active',
                ]);
            $shareUrl = url('/ref/' . $ref->referral_code);
        }

        return view('customer.giveaway-detail', compact('campaign', 'myEntry', 'leaderboard', 'shareUrl'));
    }

    public function activeCampaigns(): JsonResponse
    {
        $campaigns = GiveawayCampaign::where('status', 'active')
            ->with('merchant')
            ->withCount('entries')
            ->orderBy('ends_at', 'asc')
            ->get();

        return response()->json(['success' => true, 'campaigns' => $campaigns]);
    }

    public function enterGiveaway(Request $request, int $campaignId): JsonResponse
    {
        $user = Auth::user();
        $customer = $user->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.'], 404);
        }

        $campaign = GiveawayCampaign::where('status', 'active')->findOrFail($campaignId);

        if (!$campaign->isActive() || ($campaign->starts_at && $campaign->starts_at->isFuture())) {
            return response()->json(['success' => false, 'message' => 'This giveaway is no longer active.'], 422);
        }

        // only members of the merchant take part (their referral points and rewards live there)
        $isMember = CustomerMerchant::where('customer_id', $customer->id)->where('merchant_id', $campaign->merchant_id)->exists();
        if (!$isMember) {
            return response()->json(['success' => false, 'message' => 'Sertai merchant ini dahulu sebelum menyertai giveaway.'], 422);
        }

        $hasTerms = filled($campaign->terms);
        if ($hasTerms && !$request->boolean('accept_terms')) {
            return response()->json(['success' => false, 'message' => 'Anda mesti bersetuju dengan terma & syarat untuk menyertai.'], 422);
        }

        // max_entries caps the number of participants (entry_count grows with referrals, so it cannot be the cap)
        if ($campaign->max_entries && GiveawayEntry::where('giveaway_campaign_id', $campaignId)->count() >= $campaign->max_entries) {
            return response()->json(['success' => false, 'message' => 'This giveaway is full!'], 422);
        }

        if (GiveawayEntry::where('giveaway_campaign_id', $campaignId)->where('customer_id', $customer->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'You are already entered in this giveaway!'], 422);
        }

        try {
            $entry = GiveawayEntry::create([
                'giveaway_campaign_id' => $campaignId,
                'customer_id' => $customer->id,
                'entry_count' => 1,
                'score' => 0,
                'source' => $campaign->entry_method,
                'terms_accepted_at' => $hasTerms ? now() : null,
                'terms_hash' => $hasTerms ? hash('sha256', $campaign->terms) : null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // unique (campaign, customer): a double click raced past the check above
            return response()->json(['success' => false, 'message' => 'You are already entered in this giveaway!'], 422);
        }

        app(GiveawayScoring::class)->refresh($campaign, [$entry->id]);

        return response()->json([
            'success' => true,
            'message' => "🎉 You're entered in the giveaway!",
            'entry' => $entry->fresh(),
        ]);
    }

    public function leaderboard(int $campaignId): JsonResponse
    {
        GiveawayCampaign::whereIn('status', ['active', 'ended'])->findOrFail($campaignId);

        $leaderboard = GiveawayEntry::where('giveaway_campaign_id', $campaignId)
            ->with('customer:id,name')
            ->ranked()
            ->limit(50)
            ->get()
            ->map(fn($e, $i) => [
                'rank' => $i + 1,
                'customer_name' => $e->customer->name ?? 'Unknown',
                'score' => $e->score,
                'entries' => $e->entry_count,
                'is_winner' => $e->is_winner,
            ]);

        return response()->json(['success' => true, 'leaderboard' => $leaderboard]);
    }

    public function myEntries(): JsonResponse
    {
        $customer = Auth::user()->customer;
        if (!$customer) {
            return response()->json(['success' => true, 'entries' => []]);
        }

        $entries = GiveawayEntry::where('customer_id', $customer->id)
            ->with('campaign')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'entries' => $entries]);
    }

    public function myPrizes(): JsonResponse
    {
        $customer = Auth::user()->customer;
        if (!$customer) {
            return response()->json(['success' => true, 'prizes' => []]);
        }

        $prizes = GiveawayWinner::where('customer_id', $customer->id)
            ->with('campaign')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'prizes' => $prizes]);
    }
}
