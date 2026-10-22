<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PRD v1.2 15.3: when a PAID package has expired for more than 7 days the merchant account is read-only.
 * Reading is always allowed; writes are refused except the few that keep the business running.
 */
class CheckSubscriptionWritable
{
    /** route names that stay writable while lapsed */
    private const ALLOWED = [
        'merchant.profile.update',   // keep contact details current
        'merchant.points.approve',   // customers' pending points must still be approved/rejected
        'merchant.points.reject',
        'merchant.staff.toggle',     // lets the owner switch staff off
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $user = $request->user();
        $merchant = ($user && $user->merchant_id) ? Merchant::with('package')->find($user->merchant_id) : null;
        if (!$merchant || !$merchant->isSubscriptionLapsed()) {
            return $next($request);
        }
        if (in_array(optional($request->route())->getName(), self::ALLOWED, true)) {
            return $next($request);
        }

        $message = 'Langganan anda telah tamat. Akaun berada dalam mod baca sahaja; sila perbaharui pakej untuk membuat perubahan.';

        return $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $message], 403)
            : back()->with('error', $message);
    }
}
