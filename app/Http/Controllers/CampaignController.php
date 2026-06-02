<?php

namespace App\Http\Controllers;

use App\Models\CampaignLink;
use App\Http\Requests\Merchant\CampaignLinkRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:merchant']);
    }

    private function getMerchantId(): int
    {
        return Auth::user()->merchant_id;
    }

    // ========================
    // BLADE VIEWS
    // ========================

    public function index(): View
    {
        $merchantId = $this->getMerchantId();
        $campaigns = CampaignLink::forMerchant($merchantId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('merchant.campaigns', compact('campaigns'));
    }

    public function show(string $slug): View
    {
        $merchantId = $this->getMerchantId();
        $campaign = CampaignLink::forMerchant($merchantId)
            ->where('slug', $slug)
            ->firstOrFail();

        // Get customers registered via this campaign
        $customers = $campaign->customerMerchants()
            ->with('customer')
            ->orderBy('tied_at', 'desc')
            ->paginate(20);

        return view('merchant.campaign-detail', compact('campaign', 'customers'));
    }

    // ========================
    // JSON API
    // ========================

    public function list(): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $campaigns = CampaignLink::forMerchant($merchantId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'campaigns' => $campaigns]);
    }

    public function store(CampaignLinkRequest $request): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $merchant = Auth::user()->merchant;

        $slug = CampaignLink::generateSlug($merchant->company_name, $request->name);

        $campaign = CampaignLink::create([
            'merchant_id' => $merchantId,
            'name'        => $request->name,
            'slug'        => $slug,
            'medium'      => $request->medium,
            'status'      => 'active',
            'expires_at'  => $request->expires_at,
        ]);

        return response()->json([
            'success'  => true,
            'campaign' => $campaign,
            'message'  => 'Campaign link created!',
        ], 201);
    }

    public function update(CampaignLinkRequest $request, int $id): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $campaign = CampaignLink::forMerchant($merchantId)->findOrFail($id);

        $campaign->update($request->only(['name', 'medium', 'status', 'expires_at']));

        return response()->json([
            'success'  => true,
            'campaign' => $campaign,
            'message'  => 'Campaign updated!',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $campaign = CampaignLink::forMerchant($merchantId)->findOrFail($id);
        $campaign->delete();

        return response()->json(['success' => true, 'message' => 'Campaign deleted.']);
    }

    public function analytics(): JsonResponse
    {
        $merchantId = $this->getMerchantId();

        $campaigns = CampaignLink::forMerchant($merchantId)
            ->orderBy('visits', 'desc')
            ->get();

        $totalVisits = $campaigns->sum('visits');
        $totalRegistrations = $campaigns->sum('registrations');
        $avgConversion = $totalVisits > 0
            ? round(($totalRegistrations / $totalVisits) * 100, 1)
            : 0;

        // Top 5 by conversions
        $topCampaigns = $campaigns->sortByDesc('registrations')->take(5)->values();

        return response()->json([
            'success' => true,
            'analytics' => [
                'total_visits'       => $totalVisits,
                'total_registrations' => $totalRegistrations,
                'avg_conversion'     => $avgConversion,
                'active_campaigns'   => $campaigns->where('status', 'active')->count(),
                'top_campaigns'      => $topCampaigns,
            ],
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $campaign = CampaignLink::forMerchant($merchantId)->findOrFail($id);

        $campaign->update([
            'status' => $campaign->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success'  => true,
            'campaign' => $campaign,
            'message'  => 'Status updated!',
        ]);
    }

    /**
     * JSON API: Registration stats by date for a campaign (or all campaigns).
     * GET /merchant/api/campaigns/registrations?campaign_id=1&days=30
     */
    public function registrationStats(Request $request): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $days = min((int) $request->query('days', 30), 365);
        $campaignId = $request->query('campaign_id');

        $query = \DB::table('customer_merchant')
            ->join('campaign_links', 'campaign_links.id', '=', 'customer_merchant.campaign_link_id')
            ->where('campaign_links.merchant_id', $merchantId)
            ->where('customer_merchant.tied_at', '>=', now()->subDays($days))
            ->select(
                'campaign_links.id as campaign_id',
                'campaign_links.name as campaign_name',
                \DB::raw('DATE(customer_merchant.tied_at) as day'),
                \DB::raw('COUNT(*) as count')
            )
            ->groupBy('campaign_id', 'campaign_name', 'day')
            ->orderBy('day');

        if ($campaignId) {
            $query->where('campaign_links.id', $campaignId);
        }

        $rows = $query->get();

        // Group by campaign
        $grouped = $rows->groupBy('campaign_id')->map(function ($rows, $cid) {
            return [
                'campaign_id'   => (int) $cid,
                'campaign_name' => $rows->first()->campaign_name,
                'data'          => $rows->map(fn($r) => ['day' => $r->day, 'count' => (int) $r->count])->values(),
            ];
        })->values();

        return response()->json(['success' => true, 'campaigns' => $grouped, 'days' => $days]);
    }

}