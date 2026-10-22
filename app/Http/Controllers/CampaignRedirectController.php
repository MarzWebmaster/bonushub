<?php

namespace App\Http\Controllers;

use App\Models\CampaignLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampaignRedirectController extends Controller
{
    /**
     * Public route: /r/{slug}
     * Tracks visit count and redirects to registration page with campaign ref.
     */
    public function redirect(Request $request, string $slug): RedirectResponse
    {
        $campaign = CampaignLink::where('slug', $slug)->first();

        if (!$campaign) {
            return redirect('/')->with('error', 'Invalid campaign link.');
        }

        if (!$campaign->isActive()) {
            return redirect('/')->with('error', 'This campaign link has expired.');
        }

        // Record the click, and the visitor (salted hash of IP + browser; never the raw IP). Link-preview robots
        // (WhatsApp, Facebook, Telegram, crawlers, tools) still get the redirect but are not counted.
        if (!self::looksLikeBot((string) $request->userAgent())) {
            $campaign->recordVisit(hash('sha256', config('app.key') . '|' . $request->ip() . '|' . substr((string) $request->userAgent(), 0, 200)));
        }

        // Redirect to register page with campaign reference
        // Store slug in session so we can capture it after registration
        session(['campaign_slug' => $slug, 'campaign_merchant_id' => $campaign->merchant_id]);

        return redirect('/register?ref=' . $slug);
    }

    public static function looksLikeBot(string $userAgent): bool
    {
        return trim($userAgent) === ''
            || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|java\/|go-http|headless|monitor|uptime/i', $userAgent);
    }
}
