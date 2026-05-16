@extends('layouts.app')
@section('title', 'Global Leaderboard - BonusHub')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Global Leaderboard</h1>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Points</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Merchants</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tier</th>
                </tr>
            </thead>
            <tbody id="leaderboard-body">
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
fetch('/superadmin/leaderboard').then(r=>r.json()).then(d=>{if(d.success){let html='';d.leaderboard.forEach((e,i)=>{let medal='';if(i==0)medal='🥇';else if(i==1)medal='🥈';else if(i==2)medal='🥉';html+='<tr class=\"hover:bg-gray-50\"><td class=\"px-4 py-3 text-center text-lg\">'+medal+'</td><td class=\"px-4 py-3 font-medium\">'+e.name+'</td><td class=\"px-4 py-3 font-bold text-indigo-600\">'+e.total_points.toLocaleString()+'</td><td class=\"px-4 py-3\">'+e.merchant_count+'</td><td class=\"px-4 py-3\"><span class=\"px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700\">'+e.tier_global+'</span></td></tr>';});document.getElementById('leaderboard-body').innerHTML=html;}});
</script>
@endsection