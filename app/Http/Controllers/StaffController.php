<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\AddPointsRequest;
use App\Http\Requests\Staff\CustomerLookupRequest;
use App\Http\Requests\Staff\RedeemPointsRequest;
use App\Http\Requests\Staff\VoidTransactionRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\MerchantReward;
use App\Models\PointsTransaction;
use App\Models\Promo;
use App\Models\Redemption;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Counter console for a merchant's staff (and the merchant owner).
 *
 * Rewritten against the real schema (customers / customer_merchant / points_transactions /
 * merchant_rewards / redemptions). The previous version used tables and models that do not exist
 * (Transaction, RewardProduct, points_balances, Merchant::staff()), so add / redeem / void always failed.
 *
 * IDs: every `customer_id` here is customers.id (NOT users.id). A staff member only ever sees and
 * touches customers who joined THEIR merchant.
 */
class StaffController extends Controller
{
    /** Awards at/above this many points need the merchant's approval (staff only; the owner approves himself). */
    private const APPROVAL_THRESHOLD = 1000;

    /** Points one staff member may award in a rolling 24h without approval. */
    private const STAFF_DAILY_LIMIT = 5000;

    /** Staff may void transactions only this recent; the merchant owner has no limit. */
    private const STAFF_VOID_HOURS = 48;

    public function __construct()
    {
        $this->middleware(['auth', 'role:staff|merchant']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (($user->status ?? 'active') === 'inactive') {
                abort(403, 'Akaun ini telah dinyahaktifkan.');
            }
            if (!$user->merchant_id) {
                abort(403, 'Akaun ini tidak dikaitkan dengan mana-mana merchant.');
            }
            return $next($request);
        });
    }

    public function dashboard(): \Illuminate\Contracts\View\View
    {
        $user = auth()->user();
        $merchant = Merchant::find($user->merchant_id);
        $branch = $user->branch_id ? Branch::find($user->branch_id) : null;

        return view('staff.dashboard', [
            'merchantName'      => $merchant->company_name ?? 'Merchant',
            'branchName'        => $branch->name ?? null,
            'approvalThreshold' => self::APPROVAL_THRESHOLD,
            'isOwner'           => $user->hasRole('merchant'),
        ]);
    }

    /**
     * Find a customer of THIS merchant by phone. Customers who did not join the merchant are reported as
     * "not found" so staff cannot browse other merchants' customers.
     */
    public function customerLookup(CustomerLookupRequest $request): JsonResponse
    {
        $user = $request->user();
        $merchantId = (int) $user->merchant_id;
        $phone = preg_replace('/[\s\-()]+/', '', (string) $request->phone);

        $customer = Customer::where('phone', $phone)->first();
        $member = $customer
            ? DB::table('customer_merchant')->where('merchant_id', $merchantId)->where('customer_id', $customer->id)->first()
            : null;

        if (!$customer || !$member) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak dijumpai dalam program anda. Minta mereka sertai melalui QR kedai.',
            ], 404);
        }

        $recent = PointsTransaction::where('merchant_id', $merchantId)
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')->limit(10)
            ->get(['id', 'type', 'status', 'points', 'notes', 'created_at']);
        $voided = PointsTransaction::where('type', 'void')->whereIn('void_of_id', $recent->pluck('id'))->pluck('void_of_id')->all();
        $isOwner = $user->hasRole('merchant');
        $cutoff = now()->subHours(self::STAFF_VOID_HOURS);

        $rewards = MerchantReward::where('merchant_id', $merchantId)->where('status', 'active')
            ->orderBy('points_required')->get(['id', 'name', 'points_required', 'stock_left']);
        $balance = (float) $member->points;

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'      => $customer->id,
                'name'    => $customer->name,
                'phone'   => $customer->phone,
                'tier'    => $member->tier_per_merchant ?? 'Basic',
                'balance' => $balance,
            ],
            'transactions' => $recent->map(fn ($t) => [
                'id'      => $t->id,
                'type'    => $t->type,
                'status'  => $t->status,
                'points'  => (float) $t->points,
                'notes'   => $t->notes,
                'at'      => optional($t->created_at)->format('d M Y H:i'),
                'voidable' => in_array($t->type, ['earn', 'redeem'], true)
                    && $t->status === 'approved'
                    && !in_array($t->id, $voided, true)
                    && ($isOwner || $t->created_at >= $cutoff),
            ])->values(),
            'rewards' => $rewards->map(fn ($r) => [
                'id'              => $r->id,
                'name'            => $r->name,
                'points_required' => (float) $r->points_required,
                'stock_left'      => $r->stock_left,
                'available'       => is_null($r->stock_left) || $r->stock_left > 0,
                'affordable'      => $balance >= (float) $r->points_required,
            ])->values(),
        ]);
    }

    /**
     * Award points. Awards >= 1000 (after any multiplier), or beyond the staff member's daily limit, wait for
     * the merchant's approval (status pending_approval; MerchantAdminController::approvePoints credits them).
     */
    public function addPoints(AddPointsRequest $request): JsonResponse
    {
        $user = $request->user();
        $merchantId = (int) $user->merchant_id;
        $customerId = (int) $request->customer_id;
        $isOwner = $user->hasRole('merchant');

        $base = (int) $request->points;
        $multiplier = Promo::active()->where('merchant_id', $merchantId)->where('type', 'multiplier')->first();
        $points = $multiplier ? (int) floor($base * (float) $multiplier->value) : $base;
        if ($points < 1) {
            return response()->json(['success' => false, 'message' => 'Jumlah mata tidak sah.'], 422);
        }

        $needsApproval = false;
        $reason = null;
        if (!$isOwner) {
            if ($points >= self::APPROVAL_THRESHOLD) {
                $needsApproval = true;
                $reason = 'amount';
            } else {
                $recent = (float) PointsTransaction::where('staff_id', $user->id)->where('type', 'earn')
                    ->whereIn('status', ['approved', 'pending_approval'])
                    ->where('created_at', '>=', now()->subDay())->sum('points');
                if ($recent + $points > self::STAFF_DAILY_LIMIT) {
                    $needsApproval = true;
                    $reason = 'daily_limit';
                }
            }
        }

        $note = trim(($request->reference ? "[{$request->reference}] " : '') . ($request->notes ?: 'Points added by staff'));

        try {
            $out = DB::transaction(function () use ($merchantId, $customerId, $user, $points, $needsApproval, $isOwner, $note) {
                $member = DB::table('customer_merchant')->where('merchant_id', $merchantId)
                    ->where('customer_id', $customerId)->lockForUpdate()->first();
                if (!$member) {
                    return null;
                }

                $tx = PointsTransaction::create([
                    'customer_id' => $customerId,
                    'merchant_id' => $merchantId,
                    'branch_id'   => $user->branch_id,
                    'staff_id'    => $user->id,
                    'type'        => 'earn',
                    'points'      => $points,
                    'status'      => $needsApproval ? 'pending_approval' : 'approved',
                    'approved_by' => (!$needsApproval && $isOwner) ? $user->id : null,
                    'notes'       => $note,
                ]);

                if (!$needsApproval) {
                    DB::table('customer_merchant')->where('merchant_id', $merchantId)
                        ->where('customer_id', $customerId)->increment('points', $points);
                }

                return [$tx, (float) DB::table('customer_merchant')->where('merchant_id', $merchantId)
                    ->where('customer_id', $customerId)->value('points')];
            });
        } catch (\Throwable $e) {
            Log::error('Staff add points failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menambah mata. Sila cuba lagi.'], 500);
        }

        if ($out === null) {
            return response()->json(['success' => false, 'message' => 'Pelanggan ini bukan ahli program anda.'], 404);
        }
        [$tx, $balance] = $out;

        Log::info("Staff {$user->id} awarded {$points} pts to customer {$customerId}", [
            'merchant_id' => $merchantId, 'tx' => $tx->id, 'pending' => $needsApproval, 'multiplier' => $multiplier?->value,
        ]);

        return response()->json([
            'success'        => true,
            'message'        => $needsApproval
                ? ($reason === 'daily_limit'
                    ? "{$points} mata dihantar untuk kelulusan merchant (had harian staff dicapai)."
                    : "{$points} mata dihantar untuk kelulusan merchant (jumlah melebihi " . self::APPROVAL_THRESHOLD . ').')
                : "{$points} mata berjaya ditambah.",
            'points'         => $points,
            'needs_approval' => $needsApproval,
            'transaction_id' => $tx->id,
            'new_balance'    => $balance,
        ]);
    }

    /** Redeem a reward at the counter: deducts points, decrements stock, writes a redemption with a claim code. */
    public function redeemPoints(RedeemPointsRequest $request): JsonResponse
    {
        $user = $request->user();
        $merchantId = (int) $user->merchant_id;
        $customerId = (int) $request->customer_id;
        $quantity = (int) ($request->quantity ?? 1);

        try {
            $out = DB::transaction(function () use ($merchantId, $customerId, $quantity, $request, $user) {
                $member = DB::table('customer_merchant')->where('merchant_id', $merchantId)
                    ->where('customer_id', $customerId)->lockForUpdate()->first();
                if (!$member) {
                    return ['code' => 404, 'message' => 'Pelanggan ini bukan ahli program anda.'];
                }

                $reward = MerchantReward::where('id', $request->reward_id)->where('merchant_id', $merchantId)
                    ->where('status', 'active')->lockForUpdate()->first();
                if (!$reward) {
                    return ['code' => 404, 'message' => 'Ganjaran tidak dijumpai atau tidak aktif.'];
                }
                if (!is_null($reward->stock_left) && $reward->stock_left < $quantity) {
                    return ['code' => 422, 'message' => "Stok tidak mencukupi. Baki: {$reward->stock_left}."];
                }

                $required = (float) $reward->points_required * $quantity;
                $balance = (float) $member->points;
                if ($balance < $required) {
                    return ['code' => 422, 'message' => "Mata tidak mencukupi. Perlu: {$required}, ada: {$balance}."];
                }

                $tx = PointsTransaction::create([
                    'customer_id' => $customerId,
                    'merchant_id' => $merchantId,
                    'branch_id'   => $user->branch_id,
                    'staff_id'    => $user->id,
                    'type'        => 'redeem',
                    'points'      => -$required,
                    'status'      => 'approved',
                    'approved_by' => $user->id,
                    'notes'       => "Redeemed: {$reward->name} x{$quantity}",
                ]);

                DB::table('customer_merchant')->where('merchant_id', $merchantId)
                    ->where('customer_id', $customerId)->decrement('points', $required);
                if (!is_null($reward->stock_left)) {
                    $reward->decrement('stock_left', $quantity);
                }

                do {
                    $claim = strtoupper(Str::random(8));
                } while (Redemption::where('claim_code', $claim)->exists());

                Redemption::create([
                    'customer_id'  => $customerId,
                    'merchant_id'  => $merchantId,
                    'branch_id'    => $user->branch_id,
                    'reward_id'    => $reward->id,
                    'points_used'  => $required,
                    'cash_topup'   => 0,
                    'claim_method' => $reward->claim_type,
                    'status'       => $reward->claim_type === 'self_collect' ? 'completed' : 'approved',
                    'claim_code'   => $claim,
                    'staff_id'     => $user->id,
                    'quantity'     => $quantity,
                    'points_transaction_id' => $tx->id,
                ]);

                return ['code' => 200, 'reward' => $reward->name, 'claim' => $claim, 'tx' => $tx->id,
                    'required' => $required, 'balance' => $balance - $required];
            });
        } catch (\Throwable $e) {
            Log::error('Staff redeem failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memproses tebusan. Sila cuba lagi.'], 500);
        }

        if ($out['code'] !== 200) {
            return response()->json(['success' => false, 'message' => $out['message']], $out['code']);
        }

        Log::info("Staff {$user->id} redeemed {$out['reward']} x{$quantity} for customer {$customerId}", ['merchant_id' => $merchantId]);

        return response()->json([
            'success'        => true,
            'message'        => "{$out['reward']} x{$quantity} berjaya ditebus.",
            'claim_code'     => $out['claim'],
            'transaction_id' => $out['tx'],
            'points_deducted' => $out['required'],
            'new_balance'    => $out['balance'],
        ]);
    }

    /**
     * Hand over a reward by claim code: marks THIS merchant's pending/approved redemption as completed.
     * (Redemptions made by customers themselves wait for exactly this step.)
     */
    public function claim(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:32']);
        $user = $request->user();
        $merchantId = (int) $user->merchant_id;
        $code = strtoupper(trim((string) $request->code));

        $row = DB::table('redemptions as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->join('merchant_rewards as w', 'w.id', '=', 'r.reward_id')
            ->where('r.merchant_id', $merchantId)->where('r.claim_code', $code)
            ->select('r.id', 'r.status', 'c.name as customer', 'w.name as reward')->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Kod tuntutan tidak dijumpai.'], 404);
        }
        if ($row->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Ganjaran ini sudah diserahkan.'], 422);
        }
        if ($row->status === 'cancelled' || $row->status === 'rejected') {
            return response()->json(['success' => false, 'message' => 'Tebusan ini telah dibatalkan.'], 422);
        }

        // conditional update: two people scanning the same code cannot both succeed
        $done = DB::table('redemptions')->where('id', $row->id)->where('merchant_id', $merchantId)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'completed', 'staff_id' => $user->id, 'updated_at' => now()]);
        if (!$done) {
            return response()->json(['success' => false, 'message' => 'Ganjaran ini sudah diserahkan.'], 422);
        }

        Log::info("Redemption {$row->id} handed over by user {$user->id}", ['merchant_id' => $merchantId]);

        return response()->json([
            'success' => true,
            'message' => "{$row->reward} diserahkan kepada {$row->customer}.",
        ]);
    }

    /**
     * Void an approved earn/redeem transaction of THIS merchant by writing a reversing 'void' row.
     * The unique void_of_id column makes a second void impossible. Voiding a 'redeem' transaction also cancels its
     * redemption (so the claim code can no longer be used) and puts the quantity back into the reward's stock.
     */
    public function voidTransaction(VoidTransactionRequest $request): JsonResponse
    {
        $user = $request->user();
        $merchantId = (int) $user->merchant_id;
        $isOwner = $user->hasRole('merchant');

        try {
            $out = DB::transaction(function () use ($request, $merchantId, $user, $isOwner) {
                $tx = PointsTransaction::where('id', $request->transaction_id)->where('merchant_id', $merchantId)->lockForUpdate()->first();
                if (!$tx) {
                    return ['code' => 404, 'message' => 'Transaksi tidak dijumpai.'];
                }
                if (!in_array($tx->type, ['earn', 'redeem'], true) || $tx->status !== 'approved') {
                    return ['code' => 422, 'message' => $tx->status === 'pending_approval'
                        ? 'Transaksi ini belum diluluskan; tolak di halaman Point Approvals.'
                        : 'Hanya transaksi earn/redeem yang telah diluluskan boleh dibatalkan.'];
                }
                if (!$isOwner && $tx->created_at < now()->subHours(self::STAFF_VOID_HOURS)) {
                    return ['code' => 403, 'message' => 'Staff hanya boleh membatalkan transaksi dalam ' . self::STAFF_VOID_HOURS . ' jam. Minta merchant.'];
                }

                $member = DB::table('customer_merchant')->where('merchant_id', $merchantId)
                    ->where('customer_id', $tx->customer_id)->lockForUpdate()->first();
                if (!$member) {
                    return ['code' => 404, 'message' => 'Pelanggan tidak lagi ahli program ini.'];
                }

                $reversal = -1 * (float) $tx->points;       // earn (+) -> negative ; redeem (-) -> positive
                if ((float) $member->points + $reversal < 0) {
                    return ['code' => 422, 'message' => 'Pelanggan telah menggunakan mata ini; baki akan jadi negatif.'];
                }

                $void = new PointsTransaction([
                    'customer_id' => $tx->customer_id,
                    'merchant_id' => $merchantId,
                    'branch_id'   => $user->branch_id,
                    'staff_id'    => $user->id,
                    'type'        => 'void',
                    'points'      => $reversal,
                    'status'      => 'approved',
                    'approved_by' => $user->id,
                    'notes'       => "VOID #{$tx->id}: {$request->reason}",
                ]);
                $void->void_of_id = $tx->id;       // not in $fillable on purpose; set in the same INSERT
                $void->save();

                DB::table('customer_merchant')->where('merchant_id', $merchantId)
                    ->where('customer_id', $tx->customer_id)->increment('points', $reversal);

                if ($tx->type === 'redeem') {
                    $red = DB::table('redemptions')->where('points_transaction_id', $tx->id)->where('merchant_id', $merchantId)->lockForUpdate()->first();
                    if ($red && $red->status !== 'cancelled') {
                        DB::table('redemptions')->where('id', $red->id)->update(['status' => 'cancelled', 'updated_at' => now()]);
                        MerchantReward::where('id', $red->reward_id)->whereNotNull('stock_left')->increment('stock_left', max(1, (int) $red->quantity));
                    }
                }

                return ['code' => 200, 'id' => $tx->id, 'balance' => (float) $member->points + $reversal];
            });
        } catch (QueryException $e) {
            // unique(void_of_id): already voided
            if (str_contains($e->getMessage(), 'void_of_id')) {
                return response()->json(['success' => false, 'message' => 'Transaksi ini sudah dibatalkan.'], 422);
            }
            Log::error('Staff void failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan transaksi.'], 500);
        } catch (\Throwable $e) {
            Log::error('Staff void failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan transaksi.'], 500);
        }

        if ($out['code'] !== 200) {
            return response()->json(['success' => false, 'message' => $out['message']], $out['code']);
        }

        Log::info("Transaction {$out['id']} voided by user {$user->id}", ['merchant_id' => $merchantId, 'reason' => $request->reason]);

        return response()->json([
            'success'     => true,
            'message'     => "Transaksi #{$out['id']} dibatalkan.",
            'new_balance' => $out['balance'],
        ]);
    }
}
