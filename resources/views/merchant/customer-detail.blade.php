@extends('layouts.app')
@section('title','Customer Detail - Merchant')
@section('content')
<div class="page-container">
    {{-- Back button --}}
    <div class="mb-4">
        <a href="/merchant/customers" class="text-bonus-600 hover:text-bonus-700 text-sm font-medium">← Back to Customers</a>
    </div>

    {{-- Customer Info Card --}}
    <div class="card p-6 mb-6" id="customer-info">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-bonus-100 text-bonus-700 flex items-center justify-center text-2xl font-bold" id="avatar">?</div>
            <div>
                <h2 class="text-xl font-bold text-surface-800" id="c-name">Loading...</h2>
                <p class="text-sm text-surface-500" id="c-email"></p>
                <p class="text-sm text-surface-400" id="c-phone"></p>
            </div>
            <div class="ml-auto text-right">
                <div class="text-3xl font-bold text-bonus-600" id="c-points">0</div>
                <div class="text-sm text-surface-500">points</div>
                <span class="badge-tier mt-1" id="c-tier">Basic</span>
            </div>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600" id="s-earned">0</div>
            <div class="text-xs text-surface-400">Total Earned</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-orange-500" id="s-redeemed">0</div>
            <div class="text-xs text-surface-400">Total Redeemed</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-surface-700" id="s-txcount">0</div>
            <div class="text-xs text-surface-400">Transactions</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-sm font-medium text-surface-600" id="s-joined">-</div>
            <div class="text-xs text-surface-400">Member Since</div>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-surface-100">
            <h3 class="font-bold text-surface-800">Transaction History</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Points</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody id="tx-table">
                <tr><td colspan="5" class="text-center text-surface-400 py-8">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const customerId = window.location.pathname.split('/').pop();

fetch('/merchant/api/customers/' + customerId)
.then(r => r.json())
.then(d => {
    if (!d.success) return;
    const c = d.customer;
    const txs = d.transactions || [];

    // Customer info
    document.getElementById('c-name').textContent = c.name;
    document.getElementById('c-email').textContent = c.email || '';
    document.getElementById('c-phone').textContent = c.phone || '-';
    document.getElementById('c-points').textContent = Number(c.points).toLocaleString();
    document.getElementById('c-tier').textContent = c.tier || 'Basic';
    document.getElementById('c-tier').className = 'badge-tier ' + (c.tier || 'basic').toLowerCase();
    document.getElementById('avatar').textContent = (c.name || '?')[0].toUpperCase();
    document.getElementById('s-joined').textContent = c.tied_at || '-';

    // Stats
    let earned = 0, redeemed = 0;
    txs.forEach(t => {
        const pts = parseFloat(t.points) || 0;
        if (pts > 0) earned += pts;
        else redeemed += Math.abs(pts);
    });
    document.getElementById('s-earned').textContent = earned.toLocaleString();
    document.getElementById('s-redeemed').textContent = redeemed.toLocaleString();
    document.getElementById('s-txcount').textContent = txs.length;

    // Transactions table
    let h = '';
    if (txs.length) {
        txs.forEach(t => {
            const pts = parseFloat(t.points) || 0;
            const isEarn = pts >= 0;
            h += '<tr>'
                + '<td class="text-xs text-surface-400">' + new Date(t.created_at).toLocaleDateString('en-MY', {day:'2-digit',month:'short',year:'numeric'}) + '</td>'
                + '<td><span class="px-2 py-0.5 rounded-full text-xs font-medium ' + (isEarn ? 'bg-emerald-50 text-emerald-700' : 'bg-orange-50 text-orange-700') + '">'
                + (t.type || (isEarn ? 'earn' : 'redeem')) + '</span></td>'
                + '<td class="font-bold ' + (isEarn ? 'text-emerald-600' : 'text-orange-500') + '">' + (isEarn ? '+' : '') + pts.toLocaleString() + '</td>'
                + '<td><span class="px-2 py-0.5 rounded text-xs ' + (t.status === 'approved' ? 'bg-emerald-50 text-emerald-600' : t.status === 'pending' ? 'bg-yellow-50 text-yellow-600' : 'bg-surface-100 text-surface-500') + '">'
                + (t.status || '-') + '</span></td>'
                + '<td class="text-sm text-surface-500">' + (t.notes || '-') + '</td>'
                + '</tr>';
        });
    } else {
        h = '<tr><td colspan="5" class="text-center text-surface-400 py-8">No transactions yet</td></tr>';
    }
    document.getElementById('tx-table').innerHTML = h;
});
</script>
@endsection
