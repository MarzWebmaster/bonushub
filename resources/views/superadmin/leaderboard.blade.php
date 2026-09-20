@extends('layouts.app')
@section('title', 'Global Leaderboard - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">🏆 Global Leaderboard</h1>
            <p class="page-subtitle">Top customers across all merchants</p>
        </div>
        <div id="total-count" class="text-sm text-surface-500"></div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Customer</th>
                        <th>Points</th>
                        <th>Merchants</th>
                        <th>Tier</th>
                    </tr>
                </thead>
                <tbody id="leaderboard-body">
                    <tr><td colspan="5" class="text-center text-surface-400 py-8">
                        <svg class="w-6 h-6 animate-spin mx-auto mb-2 text-bonus-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Loading...
                    </td></tr>
                </tbody>
            </table>
        </div>

        <div id="pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-surface-200">
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const perPage = 20;

async function loadLeaderboard(page) {
    currentPage = page || 1;
    document.getElementById('leaderboard-body').innerHTML = '<tr><td colspan="5" class="text-center text-surface-400 py-8">Loading...</td></tr>';

    try {
        const resp = await fetch(`/superadmin/api/leaderboard?per_page=${perPage}&page=${currentPage}`);
        const d = await resp.json();

        if (d.success) {
            const list = d.leaderboard;
            const p = d.pagination;
            let h = '';

            if (list.length === 0) {
                h = '<tr><td colspan="5" class="text-center text-surface-400 py-8">No customers found</td></tr>';
            } else {
                list.forEach(e => {
                    let medal = '';
                    if (e.rank === 1) medal = '🥇';
                    else if (e.rank === 2) medal = '🥈';
                    else if (e.rank === 3) medal = '🥉';

                    const merchants = e.merchant_names || '-';
                    const merchantCount = e.merchant_count || 0;

                    h += `<tr class="hover:bg-surface-50">
                        <td class="text-center text-lg">${medal || e.rank}</td>
                        <td>
                            <div class="font-medium">${e.name}</div>
                            ${e.phone ? '<div class="text-xs text-surface-400">' + e.phone + '</div>' : ''}
                        </td>
                        <td class="font-bold text-bonus-600">${Number(e.total_points).toLocaleString()}</td>
                        <td>
                            <div class="text-sm text-surface-700">${merchants}</div>
                            <div class="text-xs text-surface-400">${merchantCount} merchant(s)</div>
                        </td>
                        <td><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">${e.tier_global || 'Basic'}</span></td>
                    </tr>`;
                });
            }

            document.getElementById('leaderboard-body').innerHTML = h;
            document.getElementById('total-count').textContent = p.total + ' customers';
            renderPagination(p);
        }
    } catch (e) {
        document.getElementById('leaderboard-body').innerHTML = '<tr><td colspan="5" class="text-center text-red-500 py-8">Error loading data</td></tr>';
    }
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.last_page <= 1) { el.innerHTML = ''; return; }

    let h = `<span class="text-sm text-surface-500">Page ${p.current_page} of ${p.last_page} (${p.total} total)</span>`;
    h += '<div class="flex gap-1 flex-wrap">';

    if (p.current_page > 1)
        h += `<button onclick="loadLeaderboard(${p.current_page - 1})" class="px-3 py-1.5 rounded-lg text-sm bg-surface-100 hover:bg-surface-200 text-surface-700">← Prev</button>`;
    else
        h += '<span class="px-3 py-1.5 rounded-lg text-sm bg-surface-50 text-surface-400">← Prev</span>';

    const start = Math.max(1, p.current_page - 2);
    const end = Math.min(p.last_page, p.current_page + 2);

    if (start > 1) {
        h += `<button onclick="loadLeaderboard(1)" class="px-3 py-1.5 rounded-lg text-sm bg-surface-100 hover:bg-surface-200 text-surface-700">1</button>`;
        if (start > 2) h += '<span class="px-2 py-1.5 text-surface-400">...</span>';
    }

    for (let i = start; i <= end; i++) {
        if (i === p.current_page)
            h += `<span class="px-3 py-1.5 rounded-lg text-sm bg-bonus-600 text-white font-medium">${i}</span>`;
        else
            h += `<button onclick="loadLeaderboard(${i})" class="px-3 py-1.5 rounded-lg text-sm bg-surface-100 hover:bg-surface-200 text-surface-700">${i}</button>`;
    }

    if (end < p.last_page) {
        if (end < p.last_page - 1) h += '<span class="px-2 py-1.5 text-surface-400">...</span>';
        h += `<button onclick="loadLeaderboard(${p.last_page})" class="px-3 py-1.5 rounded-lg text-sm bg-surface-100 hover:bg-surface-200 text-surface-700">${p.last_page}</button>`;
    }

    if (p.current_page < p.last_page)
        h += `<button onclick="loadLeaderboard(${p.current_page + 1})" class="px-3 py-1.5 rounded-lg text-sm bg-surface-100 hover:bg-surface-200 text-surface-700">Next →</button>`;
    else
        h += '<span class="px-3 py-1.5 rounded-lg text-sm bg-surface-50 text-surface-400">Next →</span>';

    h += '</div>';
    el.innerHTML = h;
}

loadLeaderboard(1);
</script>
@endsection