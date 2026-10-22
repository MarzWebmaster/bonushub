@extends('layouts.app')
@section('title', 'Counter - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Counter</h1>
            <p class="page-subtitle">{{ $merchantName }}@if($branchName) · {{ $branchName }}@endif — award points, redeem rewards, void mistakes</p>
        </div>
    </div>

    {{-- 1. Lookup --}}
    <div class="card p-6">
        <h2 class="font-bold text-surface-800 mb-4">Find customer</h2>
        <form id="lookup-form" class="flex flex-col sm:flex-row gap-3">
            <input id="lookup-phone" type="text" inputmode="tel" class="form-input flex-1" placeholder="Customer phone, e.g. 0123456789" required>
            <button type="submit" class="btn-primary">Search</button>
        </form>
        <p id="lookup-error" class="hidden text-sm text-red-600 mt-3"></p>
    </div>

    {{-- 1b. Hand over a reward by claim code --}}
    <div class="card p-6 mt-6">
        <h2 class="font-bold text-surface-800 mb-4">Hand over a reward (claim code)</h2>
        <form id="claim-form" class="flex flex-col sm:flex-row gap-3">
            <input id="claim-code" type="text" maxlength="32" autocomplete="off" class="form-input flex-1" style="text-transform:uppercase" placeholder="Customer's claim code, e.g. K7P2QX9A" required>
            <button type="submit" class="btn-primary">Mark as handed over</button>
        </form>
        <p id="claim-msg" class="hidden text-sm mt-3"></p>
    </div>

    <div id="panel" class="hidden mt-6 space-y-6">
        {{-- customer card --}}
        <div class="card p-6 flex items-center justify-between">
            <div>
                <p id="c-name" class="text-lg font-bold text-surface-800"></p>
                <p class="text-sm text-surface-500"><span id="c-phone"></span> · <span id="c-tier"></span></p>
            </div>
            <div class="text-right">
                <p id="c-balance" class="text-3xl font-bold text-indigo-600">0</p>
                <p class="text-xs text-surface-500">points balance</p>
            </div>
        </div>

        <div id="msg" class="hidden rounded-xl px-4 py-3 text-sm"></div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- 2. Award --}}
            <div class="card p-6">
                <h2 class="font-bold text-surface-800 mb-4">Award points</h2>
                <form id="award-form" class="space-y-4">
                    <div>
                        <label class="form-label">Points</label>
                        <input id="a-points" type="number" min="1" max="100000" class="form-input" required>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach([10, 25, 50, 100, 200, 500] as $q)
                                <button type="button" class="btn-outline px-3 py-1 text-sm" onclick="document.getElementById('a-points').value={{ $q }}">{{ $q }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div><label class="form-label">Receipt / reference (optional)</label><input id="a-ref" type="text" maxlength="100" class="form-input" placeholder="e.g. INV-00123"></div>
                    <div><label class="form-label">Notes (optional)</label><input id="a-notes" type="text" maxlength="500" class="form-input"></div>
                    <button type="submit" class="btn-primary w-full">Award points</button>
                    @unless($isOwner)
                        <p class="text-xs text-surface-500">Awards of {{ number_format($approvalThreshold) }} points or more are sent to the merchant for approval.</p>
                    @endunless
                </form>
            </div>

            {{-- 3. Redeem --}}
            <div class="card p-6">
                <h2 class="font-bold text-surface-800 mb-4">Redeem reward</h2>
                <form id="redeem-form" class="space-y-4">
                    <div id="rewards" class="space-y-2"></div>
                    <div><label class="form-label">Quantity</label><input id="r-qty" type="number" min="1" max="100" value="1" class="form-input" style="max-width:8rem"></div>
                    <button type="submit" class="btn-primary w-full" id="r-btn" disabled>Redeem</button>
                </form>
            </div>
        </div>

        {{-- 4. Recent + void --}}
        <div class="card p-6">
            <h2 class="font-bold text-surface-800 mb-4">Recent transactions</h2>
            <div id="txs" class="divide-y divide-gray-100"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const $ = (id) => document.getElementById(id);
    const fmt = (n) => Number(n).toLocaleString();
    let current = null;     // {id, phone}

    function api(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
            body: JSON.stringify(body),
        }).then(async (r) => {
            let d = {};
            try { d = await r.json(); } catch (e) {}
            if (!r.ok && !d.message) {
                const first = d.errors ? Object.values(d.errors)[0][0] : null;
                d.message = first || 'Request failed (' + r.status + ').';
            }
            if (!r.ok) d.success = false;
            return d;
        });
    }
    function flash(ok, text) {
        const m = $('msg');
        m.textContent = text;
        m.className = 'rounded-xl px-4 py-3 text-sm ' + (ok ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700');
    }
    function el(tag, cls, text) { const e = document.createElement(tag); if (cls) e.className = cls; if (text !== undefined) e.textContent = text; return e; }

    function render(d) {
        const c = d.customer;
        current = {id: c.id, phone: c.phone};
        $('c-name').textContent = c.name;
        $('c-phone').textContent = c.phone;
        $('c-tier').textContent = c.tier;
        $('c-balance').textContent = fmt(c.balance);

        const box = $('rewards'); box.textContent = '';
        if (!d.rewards.length) box.appendChild(el('p', 'text-sm text-surface-500', 'No active rewards.'));
        d.rewards.forEach((r) => {
            const ok = r.available && r.affordable;
            const lab = el('label', 'flex items-center justify-between p-3 border rounded-lg ' + (ok ? 'cursor-pointer' : 'opacity-50'));
            const left = el('div', 'flex items-center gap-3');
            const radio = el('input'); radio.type = 'radio'; radio.name = 'reward'; radio.value = r.id; radio.disabled = !ok;
            radio.addEventListener('change', () => { $('r-btn').disabled = false; });
            left.appendChild(radio);
            left.appendChild(el('span', 'text-sm font-medium', r.name + (r.available ? '' : ' (out of stock)') + (r.available && !r.affordable ? ' (not enough points)' : '')));
            lab.appendChild(left);
            lab.appendChild(el('span', 'text-sm font-bold text-indigo-600', fmt(r.points_required) + ' pts'));
            box.appendChild(lab);
        });
        $('r-btn').disabled = true;

        const t = $('txs'); t.textContent = '';
        if (!d.transactions.length) t.appendChild(el('p', 'text-sm text-surface-500', 'No transactions yet.'));
        d.transactions.forEach((x) => {
            const row = el('div', 'py-3 flex items-center justify-between gap-3');
            const info = el('div');
            info.appendChild(el('p', 'text-sm text-surface-800', '#' + x.id + ' · ' + x.type + (x.status !== 'approved' ? ' (' + x.status + ')' : '')));
            info.appendChild(el('p', 'text-xs text-surface-400', (x.at || '') + (x.notes ? ' · ' + x.notes : '')));
            row.appendChild(info);
            const right = el('div', 'flex items-center gap-3');
            right.appendChild(el('span', 'text-sm font-bold ' + (x.points >= 0 ? 'text-green-600' : 'text-red-600'), (x.points >= 0 ? '+' : '') + fmt(x.points)));
            if (x.voidable) {
                const b = el('button', 'px-3 py-1 text-xs rounded-lg bg-red-500 text-white hover:bg-red-600', 'Void');
                b.type = 'button';
                b.addEventListener('click', () => voidTx(x.id));
                right.appendChild(b);
            }
            row.appendChild(right);
            t.appendChild(row);
        });
        $('panel').classList.remove('hidden');
    }

    function lookup(phone, keepMsg) {
        $('lookup-error').classList.add('hidden');
        return api('{{ route('staff.customer.lookup') }}', {phone: phone}).then((d) => {
            if (!d.success) {
                $('panel').classList.add('hidden'); current = null;
                $('lookup-error').textContent = d.message; $('lookup-error').classList.remove('hidden');
                return;
            }
            if (!keepMsg) $('msg').className = 'hidden';
            render(d);
        });
    }

    $('lookup-form').addEventListener('submit', (e) => { e.preventDefault(); lookup($('lookup-phone').value.trim(), false); });

    $('award-form').addEventListener('submit', (e) => {
        e.preventDefault();
        if (!current) return;
        api('{{ route('staff.add.points') }}', {customer_id: current.id, points: parseInt($('a-points').value, 10), reference: $('a-ref').value, notes: $('a-notes').value})
            .then((d) => { flash(d.success, d.message); if (d.success) { $('award-form').reset(); lookup(current.phone, true); } });
    });

    $('redeem-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const sel = document.querySelector('input[name=reward]:checked');
        if (!current || !sel) return;
        api('{{ route('staff.redeem') }}', {customer_id: current.id, reward_id: parseInt(sel.value, 10), quantity: parseInt($('r-qty').value, 10) || 1})
            .then((d) => { flash(d.success, d.success ? d.message + ' Claim code: ' + d.claim_code : d.message); if (d.success) lookup(current.phone, true); });
    });

    $('claim-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const m = $('claim-msg');
        api('{{ route('staff.claim') }}', {code: $('claim-code').value.trim()}).then((d) => {
            m.textContent = d.message;
            m.className = 'text-sm mt-3 ' + (d.success ? 'text-emerald-700' : 'text-red-600');
            if (d.success) $('claim-form').reset();
        });
    });

    function voidTx(id) {
        const reason = prompt('Reason for voiding transaction #' + id + ' (required):');
        if (!reason || reason.trim().length < 3) return;
        api('{{ route('staff.void') }}', {transaction_id: id, reason: reason.trim()})
            .then((d) => { flash(d.success, d.message); if (d.success) lookup(current.phone, true); });
    }
})();
</script>
@endsection
