<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralClick;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\CustomerMerchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['referralLanding']);
    }

    // ========================
    // CUSTOMER — My Referral Links
    // ========================

    public function referralLinksPage(): View
    {
        $customer = Auth::user()->customer;
        $merchantId = Auth::user()->merchant_id ?? null;

        // Get merchant_id from customer's merchant association
        $linkedMerchant = CustomerMerchant::where('customer_id', $customer->id)->first();
        $mid = $linkedMerchant?->merchant_id;

        $referrals = Referral::forCustomer($customer->id)
            ->with('merchant')
            ->get();

        return view('customer.referrals', compact('customer', 'referrals'));
    }

    public function createReferralLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'source' => 'nullable|string|max:50',
        ]);

        $customer = Auth::user()->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.'], 422);
        }

        // Check if referral link already exists for this customer+merchant
        $existing = Referral::where('customer_id', $customer->id)
            ->where('merchant_id', $validated['merchant_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'referral' => $existing,
                'message' => 'Referral link already exists.',
            ]);
        }

        $referral = Referral::create([
            'referral_code' => Referral::generateCode($customer->id, $validated['merchant_id']),
            'customer_id' => $customer->id,
            'merchant_id' => $validated['merchant_id'],
            'source' => $validated['source'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'referral' => $referral,
            'message' => 'Referral link created!',
        ], 201);
    }

    // ========================
    // PUBLIC — Referral Landing (unauthenticated)
    // ========================

    public function referralLanding(string $code): RedirectResponse|View
    {
        $referral = Referral::where('referral_code', $code)->firstOrFail();

        // Track click
        $referral->trackClick();

        ReferralClick::create([
            'referral_id' => $referral->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'utm_source' => request('utm_source'),
            'utm_medium' => request('utm_medium'),
            'utm_campaign' => request('utm_campaign'),
        ]);

        // Store referral code in session for tracking signups
        session(['referral_code' => $code, 'referral_merchant_id' => $referral->merchant_id]);

        // Redirect to registration
        return redirect()->route('register')
            ->with('referral_info', [
                'merchant_name' => $referral->merchant->company_name ?? 'this business',
                'referred_by' => $referral->customer->name ?? 'a friend',
            ]);
    }

    // ========================
    // MERCHANT — Referral Analytics
    // ========================

    public function merchantReferralAnalytics(Request $request): JsonResponse
    {
        $merchantId = Auth::user()->merchant_id;

        $referrals = Referral::forMerchant($merchantId)->get();

        $totalClicks = $referrals->sum('total_clicks');
        $totalSignups = $referrals->sum('total_signups');
        $totalConversions = $referrals->sum('total_conversions');
        $totalPointsSpent = $referrals->sum('points_earned');
        $conversionRate = $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 1) : 0;

        $topReferrers = Referral::forMerchant($merchantId)
            ->with('customer:id,name,email')
            ->orderByDesc('total_conversions')
            ->take(10)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'customer_name' => $r->customer->name ?? 'Unknown',
                'customer_email' => $r->customer->email ?? '',
                'referral_code' => $r->referral_code,
                'clicks' => $r->total_clicks,
                'signups' => $r->total_signups,
                'conversions' => $r->total_conversions,
                'points_earned' => $r->points_earned,
            ]);

        // Clicks by source
        $clicksBySource = ReferralClick::whereIn('referral_id', $referrals->pluck('id'))
            ->selectRaw('COALESCE(utm_source, source, \'direct\') as src, COUNT(*) as count')
            ->groupBy('src')
            ->pluck('count', 'src');

        return response()->json([
            'success' => true,
            'analytics' => [
                'total_links' => $referrals->count(),
                'total_clicks' => $totalClicks,
                'total_signups' => $totalSignups,
                'total_conversions' => $totalConversions,
                'conversion_rate' => $conversionRate,
                'total_points_spent' => $totalPointsSpent,
                'top_referrers' => $topReferrers,
                'clicks_by_source' => $clicksBySource,
            ],
        ]);
    }
}
