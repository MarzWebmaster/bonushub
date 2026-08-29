<?php

namespace App\Http\Controllers;

use App\Models\GiveawayCampaign;
use App\Models\GiveawayEntry;
use App\Models\GiveawayWinner;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'prize_description' => 'required|string|max:300',
            'prize_value' => 'nullable|integer|min:0',
            'winner_count' => 'required|integer|min:1|max:100',
            'selection_method' => 'required|in:manual,random,top_referrers',
            'entry_method' => 'required|in:referral,task,purchase,manual',
            'entries_per_referral' => 'nullable|integer|min:1|max:10',
            'max_entries' => 'nullable|integer|min:10',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $merchant = $this->getMerchant();
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
            ->orderByDesc('entry_count')
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

    public function selectWinners(Request $request, int $campaignId): JsonResponse
    {
        $validated = $request->validate([
            'method' => 'required|in:manual,random,top_referrers',
            'winner_ids' => 'nullable|array',
            'winner_ids.*' => 'integer',
        ]);

        $merchant = $this->getMerchant();
        $campaign = GiveawayCampaign::where('merchant_id', $merchant->id)
            ->with('entries.customer')
            ->findOrFail($campaignId);

        if ($campaign->winners()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Winners already selected.'], 422);
        }

        $winners = [];
        DB::beginTransaction();
        try {
            switch ($validated['method']) {
                case 'random':
                    $entries = $campaign->entries()->get();
                    if ($entries->isEmpty()) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'No entries yet.'], 422);
                    }
                    $shuffled = $entries->shuffle()->take($campaign->winner_count);
                    foreach ($shuffled as $i => $entry) {
                        $winner = GiveawayWinner::create([
                            'giveaway_campaign_id' => $campaign->id,
                            'customer_id' => $entry->customer_id,
                            'giveaway_entry_id' => $entry->id,
                            'position' => $i + 1,
                            'prize_description' => $campaign->prize_description,
                        ]);
                        $entry->update(['is_winner' => true, 'prize_won' => $campaign->prize_description, 'won_at' => now()]);
                        $winners[] = $winner->fresh(['customer']);
                    }
                    break;

                case 'top_referrers':
                    $topEntries = GiveawayEntry::where('giveaway_campaign_id', $campaign->id)
                        ->orderByDesc('entry_count')
                        ->take($campaign->winner_count)
                        ->get();
                    foreach ($topEntries as $i => $entry) {
                        $winner = GiveawayWinner::create([
                            'giveaway_campaign_id' => $campaign->id,
                            'customer_id' => $entry->customer_id,
                            'giveaway_entry_id' => $entry->id,
                            'position' => $i + 1,
                            'prize_description' => $campaign->prize_description,
                        ]);
                        $entry->update(['is_winner' => true, 'prize_won' => $campaign->prize_description, 'won_at' => now()]);
                        $winners[] = $winner->fresh(['customer']);
                    }
                    break;

                case 'manual':
                    $winnerIds = $validated['winner_ids'] ?? [];
                    if (empty($winnerIds)) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Provide winner_ids for manual selection.'], 422);
                    }
                    foreach ($winnerIds as $i => $customerId) {
                        $entry = GiveawayEntry::where('giveaway_campaign_id', $campaign->id)
                            ->where('customer_id', $customerId)
                            ->first();
                        if (!$entry) continue;
                        $winner = GiveawayWinner::create([
                            'giveaway_campaign_id' => $campaign->id,
                            'customer_id' => $customerId,
                            'giveaway_entry_id' => $entry->id,
                            'position' => $i + 1,
                            'prize_description' => $campaign->prize_description,
                        ]);
                        $entry->update(['is_winner' => true, 'prize_won' => $campaign->prize_description, 'won_at' => now()]);
                        $winners[] = $winner->fresh(['customer']);
                    }
                    break;
            }

            $campaign->update([
                'status' => 'ended',
                'winners_announced_at' => now(),
            ]);

            DB::commit();
            Log::info("Giveaway winners selected for campaign #{$campaign->id}", [
                'method' => $validated['method'],
                'winner_count' => count($winners),
            ]);

            return response()->json([
                'success' => true,
                'message' => count($winners) . ' winner(s) selected! 🎉',
                'winners' => $winners,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Winner selection failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to select winners.'], 500);
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
        $campaign = GiveawayCampaign::withCount('entries')
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
            ->orderByDesc('entry_count')
            ->limit(20)
            ->get();

        return view('customer.giveaway-detail', compact('campaign', 'myEntry', 'leaderboard'));
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

        if (!$campaign->isActive()) {
            return response()->json(['success' => false, 'message' => 'This giveaway is no longer active.'], 422);
        }

        // Check if max entries reached
        if ($campaign->max_entries && $campaign->totalEntries() >= $campaign->max_entries) {
            return response()->json(['success' => false, 'message' => 'This giveaway is full!'], 422);
        }

        // Check if already entered
        $entry = GiveawayEntry::where('giveaway_campaign_id', $campaignId)
            ->where('customer_id', $customer->id)
            ->first();

        if ($entry) {
            return response()->json(['success' => false, 'message' => 'You are already entered in this giveaway!']);
        }

        $entry = GiveawayEntry::create([
            'giveaway_campaign_id' => $campaignId,
            'customer_id' => $customer->id,
            'entry_count' => 1,
            'source' => 'manual',
        ]);

        return response()->json([
            'success' => true,
            'message' => "🎉 You're entered in the giveaway!",
            'entry' => $entry,
        ]);
    }

    public function leaderboard(int $campaignId): JsonResponse
    {
        $leaderboard = GiveawayEntry::where('giveaway_campaign_id', $campaignId)
            ->with('customer:id,name')
            ->orderByDesc('entry_count')
            ->limit(50)
            ->get()
            ->map(fn($e, $i) => [
                'rank' => $i + 1,
                'customer_name' => $e->customer->name ?? 'Unknown',
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
