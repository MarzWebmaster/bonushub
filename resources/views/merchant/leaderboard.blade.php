@extends('layouts.app')
@section('title', 'Leaderboard - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Customer Leaderboard</h1>
            <p class="page-subtitle">See your top customers</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th class="w-12 text-center">#</th><th>Customer</th><th>Points</th><th>Tier</th></tr></thead>
                <tbody id="lb-table">
                    @forelse($leaderboard as $i => $e)
                    <tr onclick="window.location.href='{{ route('merchant.customers.detail', $e->customer_id) }}'" class="cursor-pointer hover:bg-bonus-50 transition-colors">
                        <td class="text-center text-lg">
                            @if($leaderboard->currentPage() == 1 && $i == 0)🥇
                            @elseif($leaderboard->currentPage() == 1 && $i == 1)🥈
                            @elseif($leaderboard->currentPage() == 1 && $i == 2)🥉
                            @else{{ ($leaderboard->currentPage()-1) * $leaderboard->perPage() + $i + 1 }}
                            @endif
                        </td>
                        <td><div class="font-medium">{{ $e->customer?->name ?? 'Unknown' }}</div></td>
                        <td class="font-bold text-bonus-600">{{ number_format($e->points) }}</td>
                        <td><span class="badge-tier {{ strtolower($e->tier_per_merchant ?? 'basic') }}">{{ $e->tier_per_merchant ?? 'Basic' }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-surface-400 py-8">No customers yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="lb-pagination" class="flex items-center justify-between px-4 py-3 border-t border-surface-200">
            @if($leaderboard->hasPages())
            <span class="text-sm text-surface-500">Showing {{ $leaderboard->firstItem() }}-{{ $leaderboard->lastItem() }} of {{ $leaderboard->total() }}</span>
            <div class="flex gap-1">
                @if($leaderboard->onFirstPage())
                    <span class="px-3 py-1 rounded bg-surface-50 text-surface-400 cursor-default text-sm">← Prev</span>
                @else
                    <button onclick="loadLB({{ $leaderboard->currentPage()-1 }})" class="px-3 py-1 rounded bg-surface-100 hover:bg-surface-200 text-surface-700 text-sm">← Prev</button>
                @endif
                @for($p=1; $p<=$leaderboard->lastPage(); $p++)
                    <button onclick="loadLB({{ $p }})" class="px-3 py-1 rounded text-sm {{ $p==$leaderboard->currentPage() ? 'bg-bonus-600 text-white' : 'bg-surface-100 hover:bg-surface-200 text-surface-700' }}">{{ $p }}</button>
                @endfor
                @if($leaderboard->hasMorePages())
                    <button onclick="loadLB({{ $leaderboard->currentPage()+1 }})" class="px-3 py-1 rounded bg-surface-100 hover:bg-surface-200 text-surface-700 text-sm">Next →</button>
                @else
                    <span class="px-3 py-1 rounded bg-surface-50 text-surface-400 cursor-default text-sm">Next →</span>
                @endif
            </div>
            @else
            <span class="text-sm text-surface-500">{{ $leaderboard->total() }} customer(s)</span><div></div>
            @endif
        </div>
    </div>
</div>
<script>
function loadLB(page) {
    fetch('/merchant/api/leaderboard?per_page=10&page=' + page).then(r => r.json()).then(d => {
        if (!d.success) return;
        const p = d.leaderboard, list = p.data, startNum = (p.current_page - 1) * p.per_page;
        let h = '';
        list.forEach((e, i) => {
            let rank = startNum + i + 1;
            let medal = rank == 1 ? '🥇' : rank == 2 ? '🥈' : rank == 3 ? '🥉' : rank;
            h += '<tr onclick="window.location.href=\'/merchant/customers/' + e.customer_id + '\'" class="cursor-pointer hover:bg-bonus-50 transition-colors"><td class="text-center text-lg">' + medal + '</td><td><div class="font-medium">' + (e.customer?.name || 'Unknown') + '</div></td><td class="font-bold text-bonus-600">' + Number(e.points).toLocaleString() + '</td><td><span class="badge-tier ' + (e.tier_per_merchant || 'basic').toLowerCase() + '">' + (e.tier_per_merchant || 'Basic') + '</span></td></tr>';
        });
        if (!list.length) h = '<tr><td colspan="4" class="text-center text-surface-400 py-8">No customers yet</td></tr>';
        document.getElementById('lb-table').innerHTML = h;
        const pg = document.getElementById('lb-pagination');
        if (p.last_page > 1) {
            let btns = '<span class="text-sm text-surface-500">Showing ' + p.from + '-' + p.to + ' of ' + p.total + '</span><div class="flex gap-1">';
            if (p.current_page > 1) btns += '<button onclick="loadLB(' + (p.current_page - 1) + ')" class="px-3 py-1 rounded bg-surface-100 hover:bg-surface-200 text-surface-700 text-sm">← Prev</button>';
            else btns += '<span class="px-3 py-1 rounded bg-surface-50 text-surface-400 cursor-default text-sm">← Prev</span>';
            for (let i = 1; i <= p.last_page; i++) btns += '<button onclick="loadLB(' + i + ')" class="px-3 py-1 rounded text-sm ' + (i === p.current_page ? 'bg-bonus-600 text-white' : 'bg-surface-100 hover:bg-surface-200 text-surface-700') + '">' + i + '</button>';
            if (p.current_page < p.last_page) btns += '<button onclick="loadLB(' + (p.current_page + 1) + ')" class="px-3 py-1 rounded bg-surface-100 hover:bg-surface-200 text-surface-700 text-sm">Next →</button>';
            else btns += '<span class="px-3 py-1 rounded bg-surface-50 text-surface-400 cursor-default text-sm">Next →</span>';
            btns += '</div>'; pg.innerHTML = btns;
        } else { pg.innerHTML = '<span class="text-sm text-surface-500">' + p.total + ' customer(s)</span><div></div>'; }
    });
}
</script>
@endsection