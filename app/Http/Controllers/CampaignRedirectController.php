<?php

namespace App\Http\Controllers;

use App\Models\CampaignLink;
use Illuminate\Http\RedirectResponse;

class CampaignRedirectController extends Controller
{
    /**
     * Public route: /r/{slug}
     * Tracks visit count and redirects to registration page with campaign ref.
     */
    public function redirect(string $slug): RedirectResponse
    {
        $campaign = CampaignLink::where('slug', $slug)->first();

        if (!$campaign) {
            return redirect('/')->with('error', 'Invalid campaign link.');
        }

        if (!$campaign->isActive()) {
            return redirect('/')->with('error', 'This campaign link has expired.');
        }

        // Record the visit
        $campaign->recordVisit();

        // Redirect to register page with campaign reference
        // Store slug in session so we can capture it after registration
        session(['campaign_slug' => $slug, 'campaign_merchant_id' => $campaign->merchant_id]);

        return redirect('/register?ref=' . $slug);
    }
}
