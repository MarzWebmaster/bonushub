@extends('layouts.app')
@section('title','Customer Detail - Merchant')
@section('content')
<div class="page-container">
    {{-- Back button --}}
    <div class="mb-4">
        <a href="/merchant/customers" class="text-bonus-600 hover:text-bonus-700 text-sm font-medium">← Back to Customers</a>
    </div>

    {{-- Customer Info Card --}}
    <div class="card p-4 sm:p-6 mb-6" id="customer-info">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-bonus-100 text-bonus-700 flex items-center justify-center text-2xl font-bold" id="avatar">?</div>
            <div>
                <h2 class="text-xl font-bold text-surface-800" id="c-name">Loading...</h2>
                <p class="text-sm text-surface-500" id="c-email"></p>
                <p class="text-sm text-surface-400" id="c-phone"></p>
            </div>
            <div class="sm:ml-auto text-left sm:text-right">
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
        <div class="overflow-x-auto"><table class="data-table">
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
        </table></div>
        <div id="tx-pagination" class="flex items-center justify-between px-4 py-3 border-t border-surface-200"></div>
    </div>
</div>

<script>
const customerId = window.location.pathname.split('/').pop();
let txPage = 1;

function loadTransactions(page) {
    txPage = page;
    fetch('/merchant/api/customers/' + customerId + '?per_page=10&page=' + page)
    .then(r => r.json())
    .then(d => {
        if (!d.success) return;
        const c = d.customer;
        const txData = d.transactions;
        const txs = txData.data || [];

        // Customer info (once)
        if (!document.getElementById('c-name').dataset.loaded) {
            document.getElementById('c-name').textContent = c.name;
            document.getElementById('c-name').dataset.loaded = '1';
            document.getElementById('c-email').textContent = c.email || '';
            document.getElementById('c-phone').textContent = c.phone || '-';
            document.getElementById('c-points').textContent = Number(c.points).toLocaleString();
            document.getElementById('c-tier').textContent = c.tier || 'Basic';
            document.getElementById('c-tier').className = 'badge-tier ' + (c.tier || 'basic').toLowerCase();
            document.getElementById('avatar').textContent = (c.name || '?')[0].toUpperCase();
            document.getElementById('s-joined').textContent = (c.tied_at || '-').substring(0, 10);
            document.getElementById('s-txcount').textContent = txData.total || 0;
        }

        // Stats computed from current page transactions
        let earned = 0, redeemed = 0;
        txs.forEach(t => { const pts = parseFloat(t.points) || 0; if (pts > 0) earned += pts; else redeemed += Math.abs(pts); });
        document.getElementById('s-earned').textContent = earned.toLocaleString();
        document.getElementById('s-redeemed').textContent = redeemed.toLocaleString();

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

        // Pagination
        let ph = '';
        let cp = txData.current_page;
        let lp = txData.last_page;
        if (lp > 1) {
            ph += '<div class="flex gap-1">';
            ph += '<button onclick="loadTransactions(' + (cp - 1) + ')" ' + (cp <= 1 ? 'disabled' : '')
                + ' class="px-3 py-1.5 text-sm rounded-lg border border-surface-200 '
                + (cp <= 1 ? 'text-surface-300 cursor-not-allowed' : 'text-surface-700 hover:bg-bonus-50') + '">Prev</button>';

            for (let p = 1; p <= lp; p++) {
                if (p === cp) {
                    ph += '<span class="px-3 py-1.5 text-sm rounded-lg bg-bonus-500 text-white font-semibold">' + p + '</span>';
                } else {
                    ph += '<button onclick="loadTransactions(' + p + ')" class="px-3 py-1.5 text-sm rounded-lg border border-surface-200 text-surface-700 hover:bg-bonus-50">' + p + '</button>';
                }
            }

            ph += '<button onclick="loadTransactions(' + (cp + 1) + ')" ' + (cp >= lp ? 'disabled' : '')
                + ' class="px-3 py-1.5 text-sm rounded-lg border border-surface-200 '
                + (cp >= lp ? 'text-surface-300 cursor-not-allowed' : 'text-surface-700 hover:bg-bonus-50') + '">Next</button>';
            ph += '</div>';
            ph += '<span class="text-xs text-surface-500">Showing ' + ((cp - 1) * 10 + 1) + '-' + Math.min(cp * 10, txData.total) + ' of ' + txData.total + '</span>';
        }
        document.getElementById('tx-pagination').innerHTML = ph;
    });
}

loadTransactions(1);
</script>
@endsection