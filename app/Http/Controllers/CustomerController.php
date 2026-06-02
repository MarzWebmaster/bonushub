<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\ProfileUpdateRequest;
use App\Http\Requests\Customer\RedeemRewardRequest;
use App\Models\MerchantReward;
use App\Models\PointsTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function profilePage(): View
    {
        return view('customer.profile');
    }

    public function pointsPage(): View
    {
        return view('customer.points');
    }

    public function rewardsPage(): View
    {
        return view('customer.rewards');
    }

    public function dashboard(): View
    {
        $user = auth()->user();
        $customer = $user->customer; // User->customer() via email match

        $customerId = $customer ? $customer->id : 0;
        $totalPoints = $customerId ? (float) \DB::table('customer_merchant')
            ->where('customer_id', $customerId)
            ->sum('points') : 0;
        $merchantCount = $customerId ? \DB::table('customer_merchant')
            ->where('customer_id', $customerId)
            ->distinct('merchant_id')
            ->count('merchant_id') : 0;
        $tier = $customer ? ($customer->tier_global ?? 'Basic') : 'Basic';
        $availableRewards = \App\Models\MerchantReward::where('status', 'active')->count();

        return view('customer.dashboard', compact('totalPoints', 'merchantCount', 'tier', 'availableRewards'));
    }

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer|staff|merchant|superadmin']);
    }

    /**
     * Get the authenticated customer's profile.
     */
    public function profileApi(Request $request): JsonResponse
    {
        $customer = $request->user()->load([
            'pointsBalances' => function ($query) {
                $query->with('merchant:id,company_name');
            },
        ]);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'tier' => $customer->tier ?? 'Bronze',
                'avatar' => $customer->avatar_url ?? null,
                'customer_merchant' => $customer->pointsBalances,
                'member_since' => $customer->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Update the authenticated customer's profile.
     */
    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
        }

        $customer->update($data);

        Log::info("Customer {$customer->id} updated their profile");

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Get points customer_merchant.points per merchant.
     */
    public function pointsBalance(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $user->customer;
        $customerId = $customer ? $customer->id : 0;

        // Points per merchant
        $customerMerchantPoints = $customerId ? DB::table('customer_merchant')
            ->join('merchants', 'merchants.id', '=', 'customer_merchant.merchant_id')
            ->where('customer_merchant.customer_id', $customerId)
            ->select('merchants.company_name as merchant', 'customer_merchant.points', 'customer_merchant.tier_per_merchant')
            ->get() : collect();

        $totalPoints = $customerMerchantPoints->sum('points');
        $merchantCount = $customerMerchantPoints->count();
        $tier = $customer ? ($customer->tier_global ?? 'Basic') : 'Basic';

        // Build history from points_transactions
        $history = $customerId ? PointsTransaction::with('merchant:id,company_name')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($tx) {
                return [
                    'merchant' => $tx->merchant->company_name ?? '-',
                    'points' => abs($tx->points),
                    'type' => $tx->type,
                    'created_at' => $tx->created_at->format('d M Y'),
                ];
            }) : collect();

        return response()->json([
            'success' => true,
            'total_points' => $totalPoints,
            'merchant_count' => $merchantCount,
            'tier' => $tier,
            'history' => $history,
            'points' => $customerMerchantPoints,
        ]);
    }

    /**
     * Get points history.
     */
    public function pointsHistory(Request $request): JsonResponse
    {
        $customerId = $request->user()->customer ? $request->user()->customer->id : 0;

        $merchantId = $request->query('merchant_id');

        $transactions = PointsTransaction::with(['merchant:id,company_name', 'staff:id,name'])
            ->where('customer_id', $customerId)
            ->when($merchantId, function ($query, $merchantId) {
                return $query->where('merchant_id', $merchantId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get available rewards for a merchant.
     */
    public function availableRewards(Request $request): JsonResponse
    {
        $merchantId = $request->query('merchant_id');

        $products = MerchantReward::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('stock_left')
                    ->orWhere('stock_left', '>', 0);
            })
            ->when($merchantId, function ($query, $merchantId) {
                return $query->where('merchant_id', $merchantId);
            })
            ->with('merchant:id,company_name')
            ->orderBy('points_required')
            ->get();

        return response()->json([
            'success' => true,
            'rewards' => $products,
        ]);
    }

    /**
     * Redeem a reward (customer self-service).
     */
    public function redeemReward(RedeemRewardRequest $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.'], 404);
        }

        $rewardProduct = MerchantReward::where('id', $request->reward_product_id)
            ->where('status', 'active')
            ->firstOrFail();

        $quantity = $request->quantity ?? 1;
        $totalPointsRequired = $rewardProduct->points_required * $quantity;

        // Check merchant matches
        if ($rewardProduct->merchant_id != $request->merchant_id) {
            return response()->json([
                'success' => false,
                'message' => 'This reward does not belong to the selected merchant.',
            ], 422);
        }

        // Check stock
        if (!is_null($rewardProduct->stock_left) && $rewardProduct->stock_left < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available: {$rewardProduct->stock_left}.",
            ], 422);
        }

        // Check customer_merchant.points
        $customerMerchantPoints = DB::table('customer_merchant')
            ->where('merchant_id', $rewardProduct->merchant_id)
            ->where('customer_id', $customer->id)
            ->value('customer_merchant.points') ?? 0;

        if ($customerMerchantPoints < $totalPointsRequired) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient points. Required: {$totalPointsRequired}, Available: {$customerMerchantPoints}.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction = PointsTransaction::create([
                'merchant_id' => $rewardProduct->merchant_id,
                'customer_id' => $customer->id,
                'staff_id' => null,
                'type' => 'debit',
                'points' => -$totalPointsRequired,
                'status' => 'approved',
                'notes' => "Self-redeemed: {$rewardProduct->name} x{$quantity}",
            ]);

            // Deduct points
            DB::table('customer_merchant')
                ->where('merchant_id', $rewardProduct->merchant_id)
                ->where('customer_id', $customer->id)
                ->decrement('customer_merchant.points', $totalPointsRequired);

            // Decrement stock
            if (!is_null($rewardProduct->stock_left)) {
                $rewardProduct->decrement('stock_left', $quantity);
            }

            DB::commit();

            Log::info("Customer {$customer->id} self-redeemed {$rewardProduct->name} x{$quantity}");

            return response()->json([
                'success' => true,
                'message' => "{$rewardProduct->name} x{$quantity} redeemed successfully!",
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer redemption failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Redemption failed. Please try again.',
            ], 500);
        }
    }

    /**
     * View the global leaderboard.
     */
    /**
     * Show the leaderboard page.
     */
    public function leaderboardPage(): View
    {
        return view("customer.leaderboard");
    }


    public function leaderboard(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 20), 100);
        $merchantId = $request->query('merchant_id');

        $query = DB::table('customer_merchant')
            ->join('customers', 'customer_merchant.customer_id', '=', 'customers.id')
            ->whereNull('customers.deleted_at')
            ->select(
                'customers.id',
                'customers.name',
                'customers.tier_global',
                DB::raw('SUM(customer_merchant.points) as total_points')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.tier_global');

        if ($merchantId) {
            $query->where('customer_merchant.merchant_id', $merchantId);
        }

        $leaderboard = $query->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get();

        // Add rank
        $ranked = $leaderboard->map(function ($entry, $index) {
            $entry->rank = $index + 1;
            return $entry;
        });

        // Get customer's own rank
        $customerId = $request->user()->customer ? $request->user()->customer->id : 0;
        $customerTotal = DB::table('customer_merchant')
            ->where('customer_id', $customerId)
            ->when($merchantId, function ($query) use ($merchantId) {
                return $query->where('merchant_id', $merchantId);
            })
            ->sum('points');

        $customerRank = DB::table(DB::raw("(
            SELECT customer_id, SUM(customer_merchant.points) as total
            FROM customer_merchant
            " . ($merchantId ? "WHERE merchant_id = {$merchantId}" : "") . "
            GROUP BY customer_id
        ) as ranks"))
            ->where('total', '>', $customerTotal)
            ->count() + 1;

        return response()->json([
            'success' => true,
            'leaderboard' => $ranked,
            'my_rank' => $customerRank,
            'my_points' => $customerTotal,
        ]);
    }
}
