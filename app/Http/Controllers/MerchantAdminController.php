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
        $mid = $merchant->id;
        $stats = [
            'total_customers' => CustomerMerchant::where('merchant_id', $mid)->count(),
            'total_points' => (int) CustomerMerchant::where('merchant_id', $mid)->sum('points'),
            'pending_approvals' => PointsTransaction::where('merchant_id', $mid)->where('status', 'pending_approval')->count(),
            'total_products' => MerchantReward::where('merchant_id', $mid)->count(),
        ];
        return view('merchant.dashboard', compact('stats'));
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

        // Append last branch name for each customer
        $customerIds = $customers->pluck('customer_id')->filter()->toArray();
        $lastBranches = [];
        if ($customerIds) {
            $latestTx = PointsTransaction::selectRaw('customer_id, MAX(id) as max_id')
                ->where('merchant_id', $merchant->id)
                ->whereIn('customer_id', $customerIds)
                ->whereNotNull('branch_id')
                ->groupBy('customer_id')
                ->pluck('max_id', 'customer_id');

            if ($latestTx->isNotEmpty()) {
                $branches = PointsTransaction::with('branch')
                    ->whereIn('id', $latestTx->values())
                    ->get()
                    ->mapWithKeys(fn($tx) => [$tx->customer_id => $tx->branch?->name]);
                $lastBranches = $branches->toArray();
            }
        }

        foreach ($customers as $cm) {
            $cm->last_branch_name = $lastBranches[$cm->customer_id] ?? null;
        }

        return view('merchant.customers', compact('customers'));
    }

    public function leaderboardPage(): View
    {
        $merchant = $this->getMerchant();
        $leaderboard = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->orderBy('points', 'desc')
            ->paginate(10);
        return view('merchant.leaderboard', compact('leaderboard'));
    }

        public function liabilityReportPage(): View
    {
        $merchant = $this->getMerchant();
        $mid = $merchant->id;
        $total_issued = (int) PointsTransaction::where('merchant_id', $mid)->where('type', 'earn')->where('status', 'approved')->sum('points');
        $total_redeemed = (int) PointsTransaction::where('merchant_id', $mid)->where('type', 'redeem')->where('status', 'approved')->sum('points');
        $report = [
            'total_issued' => $total_issued,
            'total_redeemed' => $total_redeemed,
            'outstanding' => $total_issued - $total_redeemed,
            'redemption_rate' => $total_issued > 0 ? round($total_redeemed / $total_issued * 100, 1) : 0,
            'active_customers' => CustomerMerchant::where('merchant_id', $mid)->where('points', '>', 0)->count(),
            'generated_at' => now()->toDateTimeString(),
        ];
        return view('merchant.liability', compact('report'));
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
        $perPage = min((int) $request->query('per_page', 10), 50);
        $customers = CustomerMerchant::with('customer')->where('merchant_id', $merchant->id);
        if ($request->tier) $customers->where('tier_per_merchant', $request->tier);
        $paginated = $customers->orderBy('points', 'desc')->paginate($perPage);

        // Append last branch name
        $customerIds = collect($paginated->items())->pluck('customer_id')->filter()->toArray();
        $lastBranches = [];
        if ($customerIds) {
            $latestTx = PointsTransaction::selectRaw('customer_id, MAX(id) as max_id')
                ->where('merchant_id', $merchant->id)
                ->whereIn('customer_id', $customerIds)
                ->whereNotNull('branch_id')
                ->groupBy('customer_id')
                ->pluck('max_id', 'customer_id');

            if ($latestTx->isNotEmpty()) {
                $branches = PointsTransaction::with('branch')
                    ->whereIn('id', $latestTx->values())
                    ->get()
                    ->mapWithKeys(fn($tx) => [$tx->customer_id => $tx->branch?->name]);
                $lastBranches = $branches->toArray();
            }
        }

        $result = $paginated->toArray();
        foreach ($result['data'] as $i => $item) {
            $cid = $item['customer_id'] ?? null;
            $result['data'][$i]['last_branch_name'] = $lastBranches[$cid] ?? null;
        }

        return response()->json(['success' => true, 'customers' => $result]);
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
     * Analytics page — merchant sees detailed performance.
     */
    public function analyticsPage(): View
    {
        $merchant = $this->getMerchant();
        $mid = $merchant->id;

        // Summary stats
        $totalCustomers = CustomerMerchant::where('merchant_id', $mid)->count();
        $totalPointsEarned = (int) PointsTransaction::where('merchant_id', $mid)->where('type', 'earn')->sum('points');
        $totalPointsRedeemed = (int) abs(PointsTransaction::where('merchant_id', $mid)->where('type', 'redeem')->sum('points'));
        $totalRevenue = (float) PointsTransaction::where('merchant_id', $mid)->where('type', 'earn')->sum('amount_spent');
        $totalTransactions = PointsTransaction::where('merchant_id', $mid)->count();

        // Today stats
        $todayEarned = (int) PointsTransaction::where('merchant_id', $mid)->where('type', 'earn')->whereDate('created_at', today())->sum('points');
        $todayTransactions = PointsTransaction::where('merchant_id', $mid)->whereDate('created_at', today())->count();
        $todayRevenue = (float) PointsTransaction::where('merchant_id', $mid)->where('type', 'earn')->whereDate('created_at', today())->sum('amount_spent');

        // Top 10 customers by points
        $topCustomers = CustomerMerchant::with('customer')
            ->where('merchant_id', $mid)
            ->orderByDesc('points')
            ->limit(10)
            ->get()
            ->map(function($cm) use ($mid) {
                $txCount = PointsTransaction::where('merchant_id', $mid)
                    ->where('customer_id', $cm->customer_id)
                    ->count();
                return [
                    'name' => $cm->customer->name ?? 'Unknown',
                    'points' => (int) $cm->points,
                    'tier' => $cm->tier_per_merchant,
                    'transactions' => $txCount,
                ];
            });

        // Recent 20 transactions
        $recentTransactions = PointsTransaction::with(['customer', 'branch', 'staff'])
            ->where('merchant_id', $mid)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Branch performance
        $branchStats = PointsTransaction::where('merchant_id', $mid)
            ->whereNotNull('branch_id')
            ->selectRaw('branch_id, COUNT(*) as tx_count, SUM(points) as total_points, SUM(amount_spent) as total_amount')
            ->groupBy('branch_id')
            ->get()
            ->map(function($row) use ($mid) {
                $branch = Branch::find($row->branch_id);
                return [
                    'name' => $branch->name ?? 'Unknown',
                    'transactions' => (int) $row->tx_count,
                    'points' => (int) $row->total_points,
                    'revenue' => (float) $row->total_amount,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        $stats = compact(
            'totalCustomers', 'totalPointsEarned', 'totalPointsRedeemed',
            'totalRevenue', 'totalTransactions',
            'todayEarned', 'todayTransactions', 'todayRevenue',
            'topCustomers', 'recentTransactions', 'branchStats'
        );

        return view('merchant.analytics', $stats);
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
    public function customerDetail(int $id, Request $request = null): JsonResponse
    {
        $merchant = $this->getMerchant();
        $cm = CustomerMerchant::with('customer')
            ->where('merchant_id', $merchant->id)
            ->where('customer_id', $id)
            ->firstOrFail();

        $perPage = $request ? min((int)$request->query('per_page', 10), 50) : 10;
        $transactions = PointsTransaction::with('branch')
            ->where('merchant_id', $merchant->id)
            ->where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Append branch_name to each transaction
        $txData = $transactions->toArray();
        foreach ($transactions as $i => $tx) {
            $txData['data'][$i]['branch_name'] = $tx->branch ? $tx->branch->name : null;
        }

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
            'transactions' => $txData,
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

    // ========================
    // PROFILE & BRANCHES
    // ========================

    /**
     * Merchant company profile + branches management page.
     */
    public function profilePage(): View
    {
        $merchant = $this->getMerchant();
        $branches = \App\Models\Branch::where('merchant_id', $merchant->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('merchant.profile', compact('merchant', 'branches'));
    }

    /**
     * Show edit company profile form.
     */
    public function editProfile(): View
    {
        $merchant = $this->getMerchant();
        return view('merchant.profile-edit', compact('merchant'));
    }

    /**
     * Update company profile info.
     */
    public function updateProfile(Request $request)
    {
        $merchant = $this->getMerchant();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'address'      => 'nullable|string|max:500',
        ]);

        $merchant->update($validated);

        Log::info("Merchant #{$merchant->id} updated profile", ['user_id' => Auth::id()]);

        return redirect()->route('merchant.profile')
            ->with('success', 'Profil syarikat dikemaskini.');
    }

    /**
     * Show create branch form.
     */
    public function createBranch(): View
    {
        $merchant = $this->getMerchant();
        return view('merchant.branch-create', compact('merchant'));
    }

    /**
     * Store a new branch.
     */
    public function storeBranch(Request $request)
    {
        $merchant = $this->getMerchant();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $merchant->branches()->create([
            'name'    => $validated['name'],
            'phone'   => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status'  => 'active',
        ]);

        Log::info("Merchant #{$merchant->id} created branch: {$validated['name']}");

        return redirect()->route('merchant.profile')
            ->with('success', 'Cawangan berjaya ditambah.');
    }

    /**
     * Show edit branch form.
     */
    public function editBranch($id): View
    {
        $merchant = $this->getMerchant();
        $branch = \App\Models\Branch::where('merchant_id', $merchant->id)->findOrFail($id);
        return view('merchant.branch-edit', compact('merchant', 'branch'));
    }

    /**
     * Update a branch.
     */
    public function updateBranch(Request $request, $id)
    {
        $merchant = $this->getMerchant();
        $branch = \App\Models\Branch::where('merchant_id', $merchant->id)->findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status'  => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        Log::info("Merchant #{$merchant->id} updated branch #{$branch->id}");

        return redirect()->route('merchant.profile')
            ->with('success', 'Cawangan dikemaskini.');
    }

    /**
     * Delete a branch.
     */
    public function deleteBranch($id)
    {
        $merchant = $this->getMerchant();
        $branch = \App\Models\Branch::where('merchant_id', $merchant->id)->findOrFail($id);

        $name = $branch->name;
        $branch->delete();

        Log::info("Merchant #{$merchant->id} deleted branch #{$id}: {$name}");

        return redirect()->route('merchant.profile')
            ->with('success', "Cawangan \"{$name}\" telah dipadam.");
    }

    // ========================
    // PROMO MANAGEMENT
    // ========================

    public function promosPage(): View
    {
        $merchant = $this->getMerchant();
        $promos = \App\Models\Promo::where('merchant_id', $merchant->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('merchant.promos', compact('merchant', 'promos'));
    }

    public function getPromos(): JsonResponse
    {
        $merchant = $this->getMerchant();
        $promos = \App\Models\Promo::where('merchant_id', $merchant->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'promos' => $promos]);
    }

    public function storePromo(Request $request): JsonResponse
    {
        $merchant = $this->getMerchant();
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:registration_bonus,multiplier,fixed_bonus',
            'value'     => 'required|numeric|min:0',
            'status'    => 'required|in:active,inactive',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $promo = \App\Models\Promo::create([
            'merchant_id' => $merchant->id,
            ...$validated,
        ]);

        Log::info("Merchant #{$merchant->id} created promo #{$promo->id}: {$promo->name}");

        return response()->json(['success' => true, 'promo' => $promo, 'message' => 'Promo created.']);
    }

    public function updatePromo(Request $request, $id): JsonResponse
    {
        $merchant = $this->getMerchant();
        $promo = \App\Models\Promo::where('merchant_id', $merchant->id)->findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:registration_bonus,multiplier,fixed_bonus',
            'value'     => 'required|numeric|min:0',
            'status'    => 'required|in:active,inactive',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $promo->update($validated);

        Log::info("Merchant #{$merchant->id} updated promo #{$promo->id}");

        return response()->json(['success' => true, 'promo' => $promo, 'message' => 'Promo updated.']);
    }

    public function deletePromo($id): JsonResponse
    {
        $merchant = $this->getMerchant();
        $promo = \App\Models\Promo::where('merchant_id', $merchant->id)->findOrFail($id);
        $promo->delete();

        Log::info("Merchant #{$merchant->id} deleted promo #{$id}");

        return response()->json(['success' => true, 'message' => 'Promo deleted.']);
    }

    // ========================
    // DASHBOARD API
    // ========================

    public function dashboardOverview(): JsonResponse
    {
        $merchant = $this->getMerchant();
        $merchantId = $merchant->id;

        // Stats
        $totalCustomers = DB::table('customer_merchant')->where('merchant_id', $merchantId)->count();
        $totalPoints = (int) DB::table('customer_merchant')->where('merchant_id', $merchantId)->sum('points');
        $totalRewards = MerchantReward::where('merchant_id', $merchantId)->count();
        $totalTasks = DB::table('viral_tasks')->where('merchant_id', $merchantId)->count();
        $pendingSubmissions = DB::table('task_submissions')
            ->join('viral_tasks', 'task_submissions.viral_task_id', '=', 'viral_tasks.id')
            ->where('viral_tasks.merchant_id', $merchantId)
            ->where('task_submissions.status', 'pending')
            ->count();

        // Points earned this month
        $pointsThisMonth = PointsTransaction::where('merchant_id', $merchantId)
            ->where('type', 'earn')
            ->whereMonth('created_at', now()->month)
            ->sum('points');

        // New customers this month
        $newCustomersMonth = DB::table('customer_merchant')
            ->where('merchant_id', $merchantId)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Trend calculations (vs last month)
        $pointsLastMonth = PointsTransaction::where('merchant_id', $merchantId)
            ->where('type', 'earn')
            ->whereMonth('created_at', now()->month - 1)
            ->sum('points');
        $pointsTrend = $pointsLastMonth > 0 ? round((($pointsThisMonth - $pointsLastMonth) / $pointsLastMonth) * 100) : 0;

        $newCustLastMonth = DB::table('customer_merchant')
            ->where('merchant_id', $merchantId)
            ->whereMonth('created_at', now()->month - 1)
            ->count();
        $custTrend = $newCustLastMonth > 0 ? round((($newCustomersMonth - $newCustLastMonth) / $newCustLastMonth) * 100) : 0;

        $stats = [
            ['icon' => '👥', 'label' => 'Total Customers', 'value' => number_format($totalCustomers), 'trend' => $custTrend],
            ['icon' => '⭐', 'label' => 'Points in Circulation', 'value' => number_format($totalPoints), 'trend' => $pointsTrend],
            ['icon' => '🎁', 'label' => 'Active Rewards', 'value' => $totalRewards, 'trend' => null],
            ['icon' => '📋', 'label' => 'Viral Tasks', 'value' => $totalTasks, 'trend' => null],
            ['icon' => '⏳', 'label' => 'Pending Reviews', 'value' => $pendingSubmissions, 'trend' => null],
        ];

        // Recent activity
        $recentTransactions = PointsTransaction::where('merchant_id', $merchantId)
            ->with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'icon' => $t->type === 'earn' ? '⬆️' : '⬇️',
                'title' => $t->type === 'earn' ? "Points earned: +{$t->points}" : "Points redeemed: -{$t->points}",
                'subtitle' => $t->customer ? $t->customer->name : 'Customer #' . $t->customer_id,
                'time' => $t->created_at->diffForHumans(),
            ]);

        $recentSubmissions = DB::table('task_submissions')
            ->join('viral_tasks', 'task_submissions.viral_task_id', '=', 'viral_tasks.id')
            ->join('customers', 'task_submissions.customer_id', '=', 'customers.id')
            ->where('viral_tasks.merchant_id', $merchantId)
            ->latest('task_submissions.created_at')
            ->limit(3)
            ->get()
            ->map(fn($s) => [
                'icon' => match($s->status) { 'pending' => '⏳', 'approved' => '✅', default => '❌' },
                'title' => "Task submitted: {$s->title}",
                'subtitle' => "{$s->name} — {$s->status}",
                'time' => now()->diffForHumans($s->created_at),
            ]);

        $allActivity = $recentTransactions->merge($recentSubmissions)
            ->sortByDesc(fn($a) => $a['time'])
            ->values()
            ->take(8);

        return response()->json([
            'merchant_name' => $merchant->name,
            'stats' => $stats,
            'recent_activity' => $allActivity,
        ]);
    }
}