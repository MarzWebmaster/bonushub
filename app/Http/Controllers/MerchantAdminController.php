<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\MerchantReward;
use App\Models\Customer;
use App\Models\PointsTransaction;
use App\Models\CustomerMerchant;
use App\Models\LoyaltyRate;
use App\Models\MerchantTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class MerchantAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:merchant']);
    }

    private function getMerchant()
    {
        $user = Auth::user();
        if ($user->hasRole('superadmin')) {
            $merchantId = request()->query('merchant_id');
            if ($merchantId) return Merchant::findOrFail($merchantId);
        }
        $merchant = Merchant::find($user->merchant_id);
        if (!$merchant) abort(403, 'No merchant associated.');
        return $merchant;
    }

    // ========================
    // BLADE VIEWS
    // ========================

    public function dashboard(): View
    {
        $merchant = $this->getMerchant();
        return view('merchant.dashboard', [
            'customers_count' => CustomerMerchant::where('merchant_id', $merchant->id)->count(),
            'staff_count' => $merchant->users()->count(),
            'pending_approvals' => PointsTransaction::where('merchant_id', $merchant->id)->where('status', 'pending_approval')->count(),
            'rewards_count' => MerchantReward::where('merchant_id', $merchant->id)->count(),
        ]);
    }

    public function pendingApprovalsPage(): View
    {
        $merchant = $this->getMerchant();
        $pending = PointsTransaction::with(['customer', 'staff'])
            ->where('merchant_id', $merchant->id)
            ->where('status', 'pending_approval')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('merchant.pending', compact('pending'));
    }

    public function rewardProductsPage(): View
    {
        $merchant = $this->getMerchant();
        $rewards = MerchantReward::where('merchant_id', $merchant->id)
            ->orderBy('points_required')
            ->paginate(20);
        return view('merchant.rewards', compact('rewards'));
    }

    public function customerListPage(): View
    {
        $merchant = $this->getMerchant();
        $customers = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->orderBy('points', 'desc')
            ->paginate(20);
        return view('merchant.customers', compact('customers'));
    }

    public function leaderboardPage(): View
    {
        $merchant = $this->getMerchant();
        $leaderboard = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->orderBy('points', 'desc')
            ->limit(50)
            ->get();
        return view('merchant.leaderboard', compact('leaderboard'));
    }

    public function liabilityReportPage(): View
    {
        $merchant = $this->getMerchant();
        $total_earned = PointsTransaction::where('merchant_id', $merchant->id)->where('type', 'earn')->where('status', 'approved')->sum('points');
        $total_redeemed = PointsTransaction::where('merchant_id', $merchant->id)->where('type', 'redeem')->where('status', 'approved')->sum('points');
        $liability = $total_earned - $total_redeemed;
        $customers = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->orderBy('points', 'desc')
            ->paginate(20);
        return view('merchant.liability', compact('total_earned', 'total_redeemed', 'liability', 'customers'));
    }

    public function loyaltyRatesPage(): View
    {
        $merchant = $this->getMerchant();
        $rate = LoyaltyRate::where('merchant_id', $merchant->id)->first();
        return view('merchant.rates', compact('rate'));
    }

    // ========================
    // JSON API ENDPOINTS
    // ========================

    public function pendingApprovals(Request $request)
    {
        $merchant = $this->getMerchant();
        $transactions = PointsTransaction::with(['customer', 'staff'])
            ->where('merchant_id', $merchant->id)
            ->where('status', 'pending_approval')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return response()->json(['success' => true, 'pending_count' => $transactions->total(), 'transactions' => $transactions]);
    }

    public function approvePoints($id)
    {
        $merchant = $this->getMerchant();
        $tx = PointsTransaction::where('merchant_id', $merchant->id)->where('id', $id)->where('status', 'pending_approval')->firstOrFail();
        $tx->update(['status' => 'approved']);
        CustomerMerchant::where('customer_id', $tx->customer_id)->where('merchant_id', $merchant->id)
            ->increment('points', $tx->points);
        Log::info("Points approved: tx {$tx->id} by admin " . Auth::id());
        return redirect()->back()->with('success', 'Points approved!');
    }

    public function rejectPoints(Request $request, $id)
    {
        $merchant = $this->getMerchant();
        $tx = PointsTransaction::where('merchant_id', $merchant->id)->where('id', $id)->where('status', 'pending_approval')->firstOrFail();
        $tx->update(['status' => 'rejected', 'notes' => $request->reason ?? 'Rejected by admin']);
        return redirect()->back()->with('success', 'Points rejected.');
    }

    public function rewardProducts(Request $request)
    {
        $merchant = $this->getMerchant();
        return response()->json(['success' => true, 'products' => MerchantReward::where('merchant_id', $merchant->id)->orderBy('points_required')->paginate(20)]);
    }

    public function storeRewardProduct(Request $request)
    {
        $merchant = $this->getMerchant();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'stock_quantity' => 'nullable|integer|min:0',
            'claim_type' => 'required|in:self_collect,delivery,download,access_code',
            'cash_price' => 'nullable|numeric|min:0',
            'delivery_cost' => 'nullable|in:merchant,customer',
        ]);
        $data['merchant_id'] = $merchant->id;
        $data['stock_left'] = $data['stock_quantity'] ?? 0;
        MerchantReward::create($data);
        return redirect()->back()->with('success', 'Reward product created!');
    }

    public function updateRewardProduct(Request $request, $id)
    {
        $merchant = $this->getMerchant();
        $product = MerchantReward::where('merchant_id', $merchant->id)->findOrFail($id);
        $product->update($request->validate([
            'name' => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'stock_quantity' => 'nullable|integer|min:0',
        ]));
        return response()->json(['success' => true, 'message' => 'Updated.']);
    }

    public function destroyRewardProduct($id)
    {
        $merchant = $this->getMerchant();
        MerchantReward::where('merchant_id', $merchant->id)->findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Reward product deleted.');
    }

    public function customerListByTier(Request $request)
    {
        $merchant = $this->getMerchant();
        $perPage = min((int) $request->query('per_page', 5), 50);
        $customers = CustomerMerchant::with('customer')->where('merchant_id', $merchant->id);
        if ($request->tier) $customers->where('tier_per_merchant', $request->tier);
        return response()->json(['success' => true, 'customers' => $customers->orderBy('points', 'desc')->paginate($perPage)]);
    }

    public function leaderboard(Request $request)
    {
        $merchant = $this->getMerchant();
        $perPage = min((int)$request->query('per_page', 10), 50);
        $data = CustomerMerchant::with('customer')->where('merchant_id', $merchant->id)
            ->orderBy('points', 'desc')->paginate($perPage);
        return response()->json(['success' => true, 'merchant' => $merchant->name, 'leaderboard' => $data]);
    }

    public function liabilityReport(Request $request)
    {
        $merchant = $this->getMerchant();
        $mid = $merchant->id;
        $total_issued = (int) PointsTransaction::where('merchant_id', $mid)->where('type', 'earn')->where('status', 'approved')->sum('points');
        $total_redeemed = (int) PointsTransaction::where('merchant_id', $mid)->where('type', 'redeem')->where('status', 'approved')->sum('points');
        $outstanding = $total_issued - $total_redeemed;
        $rate = $total_issued > 0 ? round($total_redeemed / $total_issued * 100, 1) : 0;
        $count = CustomerMerchant::where('merchant_id', $mid)->where('points', '>', 0)->count();
        return response()->json(['success' => true, 'report' => [
            'total_issued' => $total_issued,
            'total_redeemed' => $total_redeemed,
            'outstanding' => $outstanding,
            'redemption_rate' => $rate,
            'active_customers' => $count,
            'generated_at' => now()->toDateTimeString(),
        ]]);
    }

    public function getLoyaltyRates(Request $request)
    {
        $merchant = $this->getMerchant();
        return response()->json(['success' => true, 'rate' => LoyaltyRate::where('merchant_id', $merchant->id)->first()]);
    }

    public function updateLoyaltyRates(Request $request)
    {
        $merchant = $this->getMerchant();
        $data = $request->validate([
            'earn_rate' => 'required|numeric|min:0.01',
            'redeem_rate' => 'required|numeric|min:1',
            'min_redeem' => 'nullable|integer|min:0',
            'max_redeem' => 'nullable|integer|min:0',
        ]);
        LoyaltyRate::updateOrCreate(['merchant_id' => $merchant->id], $data);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Loyalty rates updated!', 'rate' => LoyaltyRate::where('merchant_id', $merchant->id)->first()]);
        }
        return redirect()->back()->with('success', 'Loyalty rates updated!');
    }

    public function updateLoyaltyRatesApi(Request $request)
    {
        $merchant = $this->getMerchant();
        $data = $request->validate([
            'earn_rate' => 'required|numeric|min:0.01',
            'redeem_rate' => 'required|numeric|min:1',
            'min_redeem' => 'nullable|integer|min:0',
            'max_redeem' => 'nullable|integer|min:0',
        ]);
        LoyaltyRate::updateOrCreate(['merchant_id' => $merchant->id], $data);
        return response()->json(['success' => true, 'message' => 'Loyalty rates updated!', 'rate' => LoyaltyRate::where('merchant_id', $merchant->id)->first()]);
    }
    /**
     * JSON API: Dashboard stats for merchant.
     */
    public function dashboardStats(): \Illuminate\Http\JsonResponse
    {
        $merchant = $this->getMerchant();
        $mid = $merchant->id;
        $months = 6;

        // Monthly registrations
        $registrations = DB::table('customer_merchant')
            ->where('merchant_id', $mid)
            ->where('tied_at', '>=', now()->subMonths($months))
            ->selectRaw('DATE_FORMAT(tied_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Monthly points earned
        $earned = DB::table('points_transactions')
            ->where('merchant_id', $mid)
            ->where('type', 'earn')
            ->where('created_at', '>=', now()->subMonths($months))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(points) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Monthly points redeemed (stored as negative, flip to positive)
        $redeemed = DB::table('points_transactions')
            ->where('merchant_id', $mid)
            ->where('type', 'redeem')
            ->where('created_at', '>=', now()->subMonths($months))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, ABS(SUM(points)) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Build label list (all months that appear)
        $allMonths = array_unique(array_merge(array_keys($registrations), array_keys($earned), array_keys($redeemed)));
        sort($allMonths);

        // Fill gaps with 0
        $regData = []; $earnData = []; $redeemData = [];
        foreach ($allMonths as $m) {
            $regData[] = (int) ($registrations[$m] ?? 0);
            $earnData[] = (int) ($earned[$m] ?? 0);
            $redeemData[] = (int) ($redeemed[$m] ?? 0);
        }

        return response()->json([
            'success' => true,
            'total_customers' => CustomerMerchant::where('merchant_id', $mid)->count(),
            'total_points' => (int) CustomerMerchant::where('merchant_id', $mid)->sum('points'),
            'pending_approvals' => PointsTransaction::where('merchant_id', $mid)->where('status', 'pending_approval')->count(),
            'total_products' => MerchantReward::where('merchant_id', $mid)->count(),
            'chart' => [
                'labels' => array_map(function($m) { return date('M Y', strtotime($m."-01")); }, $allMonths),
                'registrations' => $regData,
                'earned' => $earnData,
                'redeemed' => $redeemData,
            ],
        ]);
    }

    /**
     * Show single customer detail page for merchant.
     */
    public function customerDetailPage(int $id): View
    {
        $merchant = $this->getMerchant();
        $cm = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->where('customer_id', $id)
            ->firstOrFail();
        return view('merchant.customer-detail', compact('cm'));
    }

    /**
     * API: single customer detail + transaction history.
     */
    public function customerDetail(int $id): JsonResponse
    {
        $merchant = $this->getMerchant();
        $cm = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->where('customer_id', $id)
            ->firstOrFail();

        $transactions = PointsTransaction::where('merchant_id', $merchant->id)
            ->where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $cm->customer->id,
                'name' => $cm->customer->name,
                'email' => $cm->customer->email,
                'phone' => $cm->customer->phone,
                'tier' => $cm->tier_per_merchant ?? 'Basic',
                'points' => $cm->points,
                'tied_at' => $cm->tied_at,
                'campaign_link_id' => $cm->campaign_link_id,
            ],
            'transactions' => $transactions,
        ]);
    }


    // ========================
    // TIER MANAGEMENT
    // ========================

    public function tiersPage(): View
    {
        $merchant = $this->getMerchant();
        $tiers = MerchantTier::where('merchant_id', $merchant->id)->orderBy('min_points', 'asc')->get();
        return view('merchant.tiers', compact('tiers'));
    }

    public function getTiers(): JsonResponse
    {
        $merchant = $this->getMerchant();
        $tiers = MerchantTier::where('merchant_id', $merchant->id)->orderBy('min_points', 'asc')->get();
        return response()->json(['success' => true, 'tiers' => $tiers]);
    }

    public function updateTiers(Request $request): JsonResponse
    {
        $merchant = $this->getMerchant();
        $data = $request->validate([
            'tiers' => 'required|array|min:1',
            'tiers.*.tier_name' => 'required|string|in:Basic,Silver,Gold,Platinum',
            'tiers.*.min_points' => 'required|integer|min:0',
        ]);

        foreach ($data['tiers'] as $tier) {
            MerchantTier::updateOrCreate(
                ['merchant_id' => $merchant->id, 'tier_name' => $tier['tier_name']],
                ['min_points' => $tier['min_points']]
            );
        }

        // Recalculate all customer tiers for this merchant
        $tiers = MerchantTier::where('merchant_id', $merchant->id)->orderBy('min_points', 'desc')->get();
        $customers = CustomerMerchant::where('merchant_id', $merchant->id)->get();
        foreach ($customers as $cm) {
            $resolved = 'Basic';
            foreach ($tiers as $t) {
                if ($cm->points >= $t->min_points) {
                    $resolved = $t->tier_name;
                    break;
                }
            }
            $cm->update(['tier_per_merchant' => $resolved]);
        }

        return response()->json(['success' => true, 'message' => 'Tiers updated & customers recalculated']);
    }

}