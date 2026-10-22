<?php

namespace App\Http\Controllers;

use App\Models\MerchantReward;
use App\Models\PointsTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Merchant side of redemptions: customers redeem (self-service or at the counter) and receive a claim code; the
 * merchant hands the reward over (complete) or cancels it and refunds the points.
 * Statuses used: pending / approved (waiting to be handed over) -> completed | cancelled.
 */
class RedemptionController extends Controller
{
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

    public function index(Request $request): View
    {
        $merchantId = $this->merchantId();
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $rows = DB::table('redemptions as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->join('merchant_rewards as w', 'w.id', '=', 'r.reward_id')
            ->where('r.merchant_id', $merchantId)
            ->when(
                in_array($status, ['pending', 'approved', 'completed', 'cancelled'], true),
                fn ($qq) => $qq->where('r.status', $status),
                fn ($qq) => $qq->whereIn('r.status', ['pending', 'approved'])      // default: still waiting to be handed over
            )
            ->when($q !== '', fn ($qq) => $qq->where(function ($w2) use ($q) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
                $w2->where('r.claim_code', 'like', $like)->orWhere('c.name', 'like', $like)->orWhere('c.phone', 'like', $like);
            }))
            ->orderByDesc('r.id')
            ->select('r.id', 'r.claim_code', 'r.points_used', 'r.claim_method', 'r.status', 'r.created_at', 'c.name as customer', 'c.phone as phone', 'w.name as reward')
            ->paginate(25)->withQueryString();

        return view('merchant.redemptions', ['rows' => $rows, 'status' => $status, 'q' => $q]);
    }

    public function complete($id): RedirectResponse
    {
        $merchantId = $this->merchantId();
        // conditional update: a redemption can be completed once, and only by its own merchant
        $done = DB::table('redemptions')->where('id', $id)->where('merchant_id', $merchantId)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'completed', 'staff_id' => Auth::id(), 'updated_at' => now()]);

        return $done
            ? back()->with('success', 'Ganjaran ditanda sebagai telah diserahkan.')
            : back()->with('error', 'Tebusan ini tidak dijumpai atau sudah diselesaikan/dibatalkan.');
    }

    /**
     * Cancel a waiting redemption and give the points back. The refund is a 'void' ledger row (linked to the original
     * redeem row through void_of_id, so that row cannot be voided a second time) and the quantity goes back into stock.
     */
    public function cancel($id): RedirectResponse
    {
        $merchantId = $this->merchantId();

        $result = DB::transaction(function () use ($id, $merchantId) {
            $r = DB::table('redemptions')->where('id', $id)->where('merchant_id', $merchantId)
                ->whereIn('status', ['pending', 'approved'])->lockForUpdate()->first();
            if (!$r) {
                return null;
            }

            DB::table('customer_merchant')->where('merchant_id', $merchantId)->where('customer_id', $r->customer_id)->lockForUpdate()->first();

            $void = new PointsTransaction([
                'customer_id' => $r->customer_id,
                'merchant_id' => $merchantId,
                'staff_id'    => Auth::id(),
                'type'        => 'void',
                'points'      => (float) $r->points_used,
                'status'      => 'approved',
                'approved_by' => Auth::id(),
                'notes'       => "Refund of redemption #{$r->id} (claim {$r->claim_code})",
            ]);
            if ($r->points_transaction_id) {
                $void->void_of_id = $r->points_transaction_id;   // not fillable on purpose
            }
            $void->save();
            MerchantReward::where('id', $r->reward_id)->whereNotNull('stock_left')->increment('stock_left', max(1, (int) $r->quantity));
            DB::table('customer_merchant')->where('merchant_id', $merchantId)->where('customer_id', $r->customer_id)
                ->increment('points', (float) $r->points_used);
            DB::table('redemptions')->where('id', $r->id)->update(['status' => 'cancelled', 'updated_at' => now()]);

            return $r;
        });

        if (!$result) {
            return back()->with('error', 'Tebusan ini tidak dijumpai atau sudah diselesaikan/dibatalkan.');
        }

        Log::info("Redemption {$result->id} cancelled and refunded by user " . Auth::id(), ['merchant_id' => $merchantId]);

        return back()->with('success', 'Tebusan dibatalkan dan ' . number_format((float) $result->points_used) . ' mata dipulangkan kepada pelanggan.');
    }
}
