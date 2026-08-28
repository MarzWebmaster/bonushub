<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMerchantApproved
{
    /**
     * Restrict merchant access based on approval status.
     * - Unapproved merchants: can only access profile + verification
     * - Approved merchants: full access
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Superadmin always passes
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        // Non-merchant users always pass
        if (!$user->hasRole('merchant')) {
            return $next($request);
        }

        $merchant = Merchant::find($user->merchant_id);

        if (!$merchant) {
            return redirect()->route('login')->with('error', 'Merchant tidak ditemui.');
        }

        // Allowed routes for unapproved merchants
        $allowedRoutes = [
            'merchant.verification',
            'merchant.verification.upload',
            'merchant.verification.skip',
            'merchant.dashboard',
            'merchant.profile',
            'merchant.profile.update',
            'merchant.logout',
            'api.notifications',
            'api.notifications.read',
            'api.notifications.read-all',
        ];

        $currentRoute = $request->route()->getName();

        // Approved merchants — full access
        if ($merchant->isApproved()) {
            return $next($request);
        }

        // Pending/rejected merchants — limited access only
        if (in_array($currentRoute, $allowedRoutes)) {
            return $next($request);
        }

        // Redirect to verification or dashboard with warning
        return redirect()->route('merchant.dashboard')
            ->with('warning', '⚠️ Akaun anda belum diluluskan. Sila lengkapkan pengesahan dokumen atau tunggu kelulusan admin.');
    }
}
