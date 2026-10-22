<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Merchant review of referral signups that the system flagged (same-IP bursts, too many signups in an hour).
 * A flagged signup is never credited automatically (ReferralAttribution::process). The merchant decides:
 *   approve -> flags cleared, the normal 3-day / engagement validation continues
 *   reject  -> signup becomes invalid ('rejected_by_merchant'); no bonus points are ever paid
 */
class ReferralReviewController extends Controller
{
    private const FLAG_LABELS = [
        'duplicate_ip' => 'Several signups from the same network',
        'velocity'     => 'Many signups through this link within an hour',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'role:merchant']);
    }

    private function merchantId(): int
    {
        $id = (int) Auth::user()->merchant_id;
        abort_unless($id > 0, 403, 'No merchant associated.');
        return $id;
    }

    private function flagged(int $merchantId)
    {
        return DB::table('referral_signups as s')
            ->join('referrals as r', 'r.id', '=', 's.referral_id')
            ->where('r.merchant_id', $merchantId)
            ->where('s.status', 'pending')
            ->whereNotNull('s.flags')
            ->where('s.flags', '!=', '[]');
    }

    public function index(): View
    {
        $rows = $this->flagged($this->merchantId())
            ->join('customers as referrer', 'referrer.id', '=', 'r.customer_id')
            ->join('customers as referred', 'referred.id', '=', 's.referred_customer_id')
            ->orderByDesc('s.created_at')
            ->limit(200)
            ->get(['s.id', 's.flags', 's.created_at', 'r.referral_code',
                   'referrer.name as referrer_name', 'referrer.email as referrer_email',
                   'referred.name as referred_name', 'referred.email as referred_email']);

        $rows = $rows->map(function ($r) {
            $r->flag_labels = collect(json_decode($r->flags, true) ?: [])->map(fn ($f) => self::FLAG_LABELS[$f] ?? $f)->all();
            return $r;
        });

        return view('merchant.referral-review', ['rows' => $rows]);
    }

    public function approve($id): RedirectResponse
    {
        $row = $this->flagged($this->merchantId())->where('s.id', $id)->first(['s.id']);
        $n = $row ? DB::table('referral_signups')->where('id', $row->id)->where('status', 'pending')->update(['flags' => null, 'updated_at' => now()]) : 0;
        if (!$n) {
            return back()->with('error', 'Rujukan ini tidak dijumpai atau sudah disemak.');
        }
        Log::info("Referral signup {$id} approved by merchant review", ['user_id' => Auth::id()]);

        return back()->with('success', 'Rujukan diluluskan. Ia akan disahkan seperti biasa apabila pelanggan itu aktif.');
    }

    public function reject($id): RedirectResponse
    {
        $row = $this->flagged($this->merchantId())->where('s.id', $id)->first(['s.id']);
        $n = $row ? DB::table('referral_signups')->where('id', $row->id)->where('status', 'pending')
            ->update(['status' => 'invalid', 'invalid_reason' => 'rejected_by_merchant', 'updated_at' => now()]) : 0;
        if (!$n) {
            return back()->with('error', 'Rujukan ini tidak dijumpai atau sudah disemak.');
        }
        Log::info("Referral signup {$id} rejected by merchant review", ['user_id' => Auth::id()]);

        return back()->with('success', 'Rujukan ditolak. Tiada mata bonus akan diberikan.');
    }
}
