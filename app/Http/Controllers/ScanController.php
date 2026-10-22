<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerMerchant;
use App\Models\PointsTransaction;
use App\Models\LoyaltyRate;
use App\Models\Promo;
use App\Traits\SendsEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    use SendsEmails;
    /**
     * Show scan page — customer has scanned a QR code.
     * URL: /scan/{merchant_id}/{branch_id?}
     */
    public function show(Request $request, $merchantId, $branchId = null)
    {
        $merchant = Merchant::where('id', $merchantId)
            ->where('status', 'active')
            ->firstOrFail();

        $branch = $branchId ? Branch::where('id', $branchId)
            ->where('merchant_id', $merchantId)
            ->where('status', 'active')
            ->first() : null;

        // Get loyalty rate for this merchant
        $loyaltyRate = LoyaltyRate::where('merchant_id', $merchantId)->first();
        $earnRate = $loyaltyRate ? $loyaltyRate->earn_rate : 100; // default 100 pts = RM1

        return view('scan', compact('merchant', 'branch', 'earnRate'));
    }

    /**
     * Process points earning — AJAX POST.
     */
    public function earn(Request $request)
    {
        $request->validate([
            'merchant_id' => 'required|integer|exists:merchants,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please login first.'], 401);
        }

        // Get customer via User->customer relationship (linked by email)
        $customer = $user->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found. Please register as a customer first.'], 404);
        }

        $merchant = Merchant::where('id', $request->merchant_id)
            ->where('status', 'active')
            ->first();

        if (!$merchant) {
            return response()->json(['success' => false, 'message' => 'Merchant not found.'], 404);
        }

        // Check customer has joined this merchant
        $customerMerchant = CustomerMerchant::where('customer_id', $customer->id)
            ->where('merchant_id', $merchant->id)
            ->first();

        if (!$customerMerchant) {
            return response()->json([
                'success' => false,
                'message' => 'You have not joined this merchant yet.',
                'redirect' => route('customer.merchants'),
            ], 403);
        }

        // Get loyalty rate
        $loyaltyRate = LoyaltyRate::where('merchant_id', $merchant->id)->first();
        $earnRate = $loyaltyRate ? $loyaltyRate->earn_rate : 100;
        $points = floor($request->amount * (100 / $earnRate));

        // Check for multiplier promo
        $multiplier = Promo::where('merchant_id', $merchant->id)
            ->where('type', 'multiplier')
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        $originalPoints = $points;
        if ($multiplier) {
            $points = (int)($points * $multiplier->value);
        }

        // Minimum points
        if ($points < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Amount too small to earn points.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create points transaction
            $transaction = PointsTransaction::create([
                'customer_id' => $customer->id,
                'merchant_id' => $merchant->id,
                'branch_id' => $request->branch_id,
                'type' => 'earn',
                'points' => $points,
                'amount_spent' => $request->amount,
                // SECURITY: the amount is self-reported by the customer, so it must never
                // credit the balance directly. The merchant approves it via the existing
                // pending-approval flow (MerchantAdminController::approvePoints), which
                // is what increments customer_merchant.points.
                'status' => 'pending_approval',
                'notes' => $request->description ?: 'Points earned via QR scan',
            ]);

            DB::commit();

            Log::info("Customer {$customer->id} requested {$points} points from merchant {$merchant->id} (pending approval)", [
                'amount_spent' => $request->amount,
                'transaction_id' => $transaction->id,
                'original_points' => $originalPoints,
                'multiplier' => $multiplier?->value,
            ]);

            return response()->json([
                'success' => true,
                'pending' => true,
                'message' => "Permohonan {$points} mata telah dihantar. Mata akan dikreditkan selepas merchant meluluskan.",
                'points_requested' => $points,
                'original_points' => $originalPoints,
                'multiplier' => $multiplier?->value,
                'new_balance' => $customerMerchant->points,
                'merchant' => $merchant->company_name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to earn points: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process points. Please try again.',
            ], 500);
        }
    }
}
