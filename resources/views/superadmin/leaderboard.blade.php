@extends('layouts.app')
@section('title', 'Global Leaderboard - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Global Leaderboard</h1>
            <p class="page-subtitle">Top customers across all merchants</p>
        </div>
    </div>
    <div class="overflow-x-auto"><table class="data-table">
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
            <tr><td colspan="5" class="text-center text-surface-400 py-8">Loading...</td></tr>
        </tbody>
    </table></div>
</div>
<script>
fetch('/superadmin/api/leaderboard?limit=50')
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            let h = '';
            d.leaderboard.forEach((e, i) => {
                let medal = '';
                if (i === 0) medal = '\u{1F947}';
                else if (i === 1) medal = '\u{1F948}';
                else if (i === 2) medal = '\u{1F949}';
                h += '<tr class="hover:bg-surface-50">' +
                    '<td class="text-center text-lg">' + (medal || (i + 1)) + '</td>' +
                    '<td class="font-medium">' + e.name + '</td>' +
                    '<td class="font-bold text-bonus-600">' + Number(e.total_points).toLocaleString() + '</td>' +
                    '<td class="text-surface-500">' + e.merchant_count + '</td>' +
                    '<td><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">' + (e.tier_global || 'Basic') + '</span></td>' +
                    '</tr>';
            });
            document.getElementById('leaderboard-body').innerHTML = h;
        }
    });
</script>
@endsection