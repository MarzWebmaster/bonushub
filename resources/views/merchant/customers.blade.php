@extends('layouts.app')
@section('title','Customers - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Customers</h1>
            <p class="page-subtitle">View and manage your customers</p>
        </div>
    </div>

    {{-- Tier filter tabs --}}
    <div class="flex gap-2 mb-4 flex-wrap">
        <button onclick="loadCustomers(1,'')" class="tier-tab active" data-tier="">All</button>
        <button onclick="loadCustomers(1,'basic')" class="tier-tab" data-tier="basic">Basic</button>
        <button onclick="loadCustomers(1,'silver')" class="tier-tab" data-tier="silver">Silver</button>
        <button onclick="loadCustomers(1,'gold')" class="tier-tab" data-tier="gold">Gold</button>
        <button onclick="loadCustomers(1,'platinum')" class="tier-tab" data-tier="platinum">Platinum</button>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Points</th>
                    <th>Tier</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody id="cust-table">
                <tr><td colspan="5" class="text-center text-surface-400 py-8">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div id="pagination" class="flex justify-between items-center mt-4 text-sm text-surface-500"></div>
</div>

<style>
.tier-tab { padding:6px 16px; border-radius:8px; font-size:0.85rem; font-weight:500;
    background:var(--color-surface-100); color:var(--color-surface-600); border:1px solid var(--color-surface-200); cursor:pointer; transition:all .2s; }
.tier-tab:hover { background:var(--color-surface-200); }
.tier-tab.active { background:var(--color-bonus-600); color:#fff; border-color:var(--color-bonus-600); }
#cust-table tr { cursor:pointer; transition:background .15s; }
#cust-table tr:hover { background:var(--color-surface-50); }
</style>

<script>
let currentTier = '';
let currentPage = 1;

function loadCustomers(page, tier) {
    if (tier !== undefined) currentTier = tier;
    currentPage = page || 1;

    // Update active tab
    document.querySelectorAll('.tier-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.tier === currentTier);
    });

    let url = '/merchant/api/customers?per_page=5&page=' + currentPage;
    if (currentTier) url += '&tier=' + currentTier;

    fetch(url).then(r => r.json()).then(d => {
        const p = d.customers || {};
        const list = p.data || [];
        let h = '';

        if (list.length) {
            list.forEach(c => {
                const cu = c.customer || c;
                h += '<tr onclick="window.location.href=\'/merchant/customers/' + cu.id + '\'">'
                    + '<td><div class="font-medium">' + (cu.name || 'N/A') + '</div>'
                    + '<div class="text-xs text-surface-400">' + (cu.email || '') + '</div></td>'
                    + '<td class="text-surface-500">' + (cu.phone || '-') + '</td>'
                    + '<td class="font-bold text-bonus-600">' + Number(c.points).toLocaleString() + '</td>'
                    + '<td><span class="badge-tier ' + (c.tier_per_merchant || 'basic').toLowerCase() + '">'
                    + (c.tier_per_merchant || 'Basic') + '</span></td>'
                    + '<td class="text-surface-400 text-xs">' + (c.tied_at || '-') + '</td>'
                    + '</tr>';
            });
        } else {
            h = '<tr><td colspan="5" class="text-center text-surface-400 py-8">No customers found</td></tr>';
        }
        document.getElementById('cust-table').innerHTML = h;

        // Pagination controls
        const pg = document.getElementById('pagination');
        if (p.last_page && p.last_page > 1) {
            let btns = '<span>Showing ' + p.from + '-' + p.to + ' of ' + p.total + '</span><div class="flex gap-1">';
            if (p.current_page > 1)
                btns += '<button onclick="loadCustomers(' + (p.current_page - 1) + ')" class="px-3 py-1 rounded bg-surface-100 hover:bg-surface-200">← Prev</button>';
            for (let i = 1; i <= p.last_page; i++) {
                btns += '<button onclick="loadCustomers(' + i + ')" class="px-3 py-1 rounded ' +
                    (i === p.current_page ? 'bg-bonus-600 text-white' : 'bg-surface-100 hover:bg-surface-200') + '">' + i + '</button>';
            }
            if (p.current_page < p.last_page)
                btns += '<button onclick="loadCustomers(' + (p.current_page + 1) + ')" class="px-3 py-1 rounded bg-surface-100 hover:bg-surface-200">Next →</button>';
            btns += '</div>';
            pg.innerHTML = btns;
        } else {
            pg.innerHTML = '<span>' + (p.total || list.length) + ' customer(s)</span><div></div>';
        }
    });
}

loadCustomers(1, '');
</script>
@endsection
