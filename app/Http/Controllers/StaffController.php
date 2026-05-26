<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\AddPointsRequest;
use App\Http\Requests\Staff\CustomerLookupRequest;
use App\Http\Requests\Staff\RedeemPointsRequest;
use App\Http\Requests\Staff\VoidTransactionRequest;
use App\Models\User;
use App\Models\Transaction;
use App\Models\RewardProduct;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:staff|merchant|superadmin']);
    }

    /**
     * Show the staff dashboard.
     */
    public function dashboard(): \Illuminate\Contracts\View\View
    {
        return view('staff.dashboard');
    }

    /**
     * Look up a customer by phone number.
     */
    public function customerLookup(CustomerLookupRequest $request): JsonResponse
    {
        $customer = User::role('Customer')
            ->where('phone', $request->phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No customer found with that phone number.',
            ], 404);
        }

        // Load points balance per merchant
        $customer->load([
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
                'points_balances' => $customer->pointsBalances,
            ],
        ]);
    }

    /**
     * Add points to a customer's account.
     * Small amounts (< 1000) are auto-approved.
     * Large amounts (>= 1000) require admin approval.
     */
    public function addPoints(AddPointsRequest $request): JsonResponse
    {
        $staff = $request->user();
        $merchant = $this->getStaffMerchant($staff);

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member is not assigned to any merchant.',
            ], 403);
        }

        $points = $request->points;
        $needsApproval = $points >= 1000;

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'merchant_id' => $merchant->id,
                'customer_id' => $request->customer_id,
                'staff_id' => $staff->id,
                'type' => 'credit',
                'points' => $points,
                'status' => $needsApproval ? 'pending_approval' : 'approved',
                'reason' => $request->reason ?? 'Points added by staff',
                'metadata' => json_encode([
                    'added_by' => $staff->name,
                    'needs_approval' => $needsApproval,
                ]),
            ]);

            // For small amounts, update balance immediately
            if (!$needsApproval) {
                $this->updatePointsBalance($merchant->id, $request->customer_id, $points);
            }

            DB::commit();

            $message = $needsApproval
                ? "{$points} points added pending approval (threshold exceeded)."
                : "{$points} points successfully added.";

            Log::info("Staff {$staff->id} added {$points} points to customer {$request->customer_id}", [
                'merchant_id' => $merchant->id,
                'needs_approval' => $needsApproval,
                'transaction_id' => $transaction->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'transaction' => $transaction,
                'needs_approval' => $needsApproval,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add points: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add points. Please try again.',
            ], 500);
        }
    }

    /**
     * Redeem points for a customer.
     */
    public function redeemPoints(RedeemPointsRequest $request): JsonResponse
    {
        $staff = $request->user();
        $merchant = $this->getStaffMerchant($staff);

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member is not assigned to any merchant.',
            ], 403);
        }

        $rewardProduct = RewardProduct::findOrFail($request->reward_product_id);
        $quantity = $request->quantity ?? 1;
        $totalPointsRequired = $rewardProduct->points_required * $quantity;

        // Check customer has enough points for this merchant
        $balance = $this->getCustomerMerchantBalance($merchant->id, $request->customer_id);

        if ($balance < $totalPointsRequired) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient points. Required: {$totalPointsRequired}, Available: {$balance}.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'merchant_id' => $merchant->id,
                'customer_id' => $request->customer_id,
                'staff_id' => $staff->id,
                'type' => 'debit',
                'points' => -$totalPointsRequired,
                'status' => 'approved',
                'reason' => "Redeemed: {$rewardProduct->name} x{$quantity}",
                'metadata' => json_encode([
                    'reward_product_id' => $rewardProduct->id,
                    'reward_product_name' => $rewardProduct->name,
                    'quantity' => $quantity,
                    'redeemed_by' => $staff->name,
                ]),
            ]);

            // Deduct points
            $this->updatePointsBalance($merchant->id, $request->customer_id, -$totalPointsRequired);

            // Decrement stock if tracked
            if (!is_null($rewardProduct->stock)) {
                $rewardProduct->decrement('stock', $quantity);
            }

            DB::commit();

            Log::info("Staff {$staff->id} redeemed {$totalPointsRequired} points for customer {$request->customer_id}");

            return response()->json([
                'success' => true,
                'message' => "{$rewardProduct->name} x{$quantity} redeemed successfully.",
                'transaction' => $transaction,
                'points_deducted' => $totalPointsRequired,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to redeem points: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem points. Please try again.',
            ], 500);
        }
    }

    /**
     * Void a transaction.
     */
    public function voidTransaction(VoidTransactionRequest $request): JsonResponse
    {
        $staff = $request->user();
        $transaction = Transaction::findOrFail($request->transaction_id);

        if ($transaction->status === 'voided') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction has already been voided.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction->update([
                'status' => 'voided',
                'metadata' => json_encode(array_merge(
                    json_decode($transaction->metadata ?? '{}', true) ?: [],
                    [
                        'voided_by' => $staff->id,
                        'voided_at' => now()->toDateTimeString(),
                        'void_reason' => $request->reason,
                    ]
                )),
            ]);

            // Reverse the points balance
            $pointsAdjustment = $transaction->type === 'credit'
                ? -abs($transaction->points)
                : abs($transaction->points);

            $this->updatePointsBalance(
                $transaction->merchant_id,
                $transaction->customer_id,
                $pointsAdjustment
            );

            DB::commit();

            Log::info("Transaction {$transaction->id} voided by staff {$staff->id}");

            return response()->json([
                'success' => true,
                'message' => 'Transaction voided successfully.',
                'transaction' => $transaction->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to void transaction: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to void transaction. Please try again.',
            ], 500);
        }
    }

    /**
     * Get the merchant this staff member belongs to.
     */
    private function getStaffMerchant(User $staff): ?Merchant
    {
        return Merchant::whereHas('staff', function ($query) use ($staff) {
            $query->where('user_id', $staff->id);
        })->first();
    }

    /**
     * Get a customer's points balance for a specific merchant.
     */
    private function getCustomerMerchantBalance(int $merchantId, int $customerId): int
    {
        $balance = DB::table('points_balances')
            ->where('merchant_id', $merchantId)
            ->where('customer_id', $customerId)
            ->value('balance');

        return $balance ?? 0;
    }

    /**
     * Update a customer's points balance for a merchant.
     */
    private function updatePointsBalance(int $merchantId, int $customerId, int $points): void
    {
        DB::table('points_balances')
            ->updateOrInsert(
                ['merchant_id' => $merchantId, 'customer_id' => $customerId],
                [
                    'balance' => DB::raw("COALESCE(balance, 0) + {$points}"),
                    'updated_at' => now(),
                ]
            );
    }
}
