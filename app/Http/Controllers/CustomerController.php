<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\ProfileUpdateRequest;
use App\Http\Requests\Customer\RedeemRewardRequest;
use App\Models\RewardProduct;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:Customer|Staff|Shop Admin|Superadmin']);
    }

    /**
     * Get the authenticated customer's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $customer = $request->user()->load([
            'pointsBalances' => function ($query) {
                $query->with('merchant:id,name');
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
                'points_balances' => $customer->pointsBalances,
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
     * Get points balance per merchant.
     */
    public function pointsBalance(Request $request): JsonResponse
    {
        $customer = $request->user();

        $balances = $customer->pointsBalances()
            ->with('merchant:id,name')
            ->get();

        $totalPoints = $balances->sum('balance');

        return response()->json([
            'success' => true,
            'total_points' => $totalPoints,
            'balances' => $balances,
        ]);
    }

    /**
     * Get points history.
     */
    public function pointsHistory(Request $request): JsonResponse
    {
        $customer = $request->user();

        $merchantId = $request->query('merchant_id');

        $transactions = Transaction::with(['merchant:id,name', 'staff:id,name'])
            ->where('customer_id', $customer->id)
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

        $products = RewardProduct::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('stock')
                    ->orWhere('stock', '>', 0);
            })
            ->when($merchantId, function ($query, $merchantId) {
                return $query->where('merchant_id', $merchantId);
            })
            ->with('merchant:id,name')
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
        $customer = $request->user();

        $rewardProduct = RewardProduct::where('id', $request->reward_product_id)
            ->where('is_active', true)
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
        if (!is_null($rewardProduct->stock) && $rewardProduct->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available: {$rewardProduct->stock}.",
            ], 422);
        }

        // Check balance
        $balance = DB::table('points_balances')
            ->where('merchant_id', $rewardProduct->merchant_id)
            ->where('customer_id', $customer->id)
            ->value('balance') ?? 0;

        if ($balance < $totalPointsRequired) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient points. Required: {$totalPointsRequired}, Available: {$balance}.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'merchant_id' => $rewardProduct->merchant_id,
                'customer_id' => $customer->id,
                'staff_id' => null,
                'type' => 'debit',
                'points' => -$totalPointsRequired,
                'status' => 'approved',
                'reason' => "Self-redeemed: {$rewardProduct->name} x{$quantity}",
                'metadata' => json_encode([
                    'reward_product_id' => $rewardProduct->id,
                    'reward_product_name' => $rewardProduct->name,
                    'quantity' => $quantity,
                    'redeemed_by_customer' => true,
                ]),
            ]);

            // Deduct points
            DB::table('points_balances')
                ->where('merchant_id', $rewardProduct->merchant_id)
                ->where('customer_id', $customer->id)
                ->decrement('balance', $totalPointsRequired);

            // Decrement stock
            if (!is_null($rewardProduct->stock)) {
                $rewardProduct->decrement('stock', $quantity);
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
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 20), 100);
        $merchantId = $request->query('merchant_id');

        $query = DB::table('points_balances')
            ->join('users', 'points_balances.customer_id', '=', 'users.id')
            ->where('users.deleted_at', null)
            ->select(
                'users.id',
                'users.name',
                'users.tier',
                DB::raw('SUM(points_balances.balance) as total_points')
            )
            ->groupBy('users.id', 'users.name', 'users.tier');

        if ($merchantId) {
            $query->where('points_balances.merchant_id', $merchantId);
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
        $customer = $request->user();
        $customerTotal = DB::table('points_balances')
            ->where('customer_id', $customer->id)
            ->when($merchantId, function ($query) use ($merchantId) {
                return $query->where('merchant_id', $merchantId);
            })
            ->sum('balance');

        $customerRank = DB::table(DB::raw("(
            SELECT customer_id, SUM(balance) as total
            FROM points_balances
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
