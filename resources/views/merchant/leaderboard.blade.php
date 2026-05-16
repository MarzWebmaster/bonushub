@extends('layouts.app')@section('title','Leaderboard - Merchant')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">Customer Leaderboard</h1><p class="page-subtitle">See your top customers</p></div></div>
<div class="card overflow-hidden"><table class="data-table"><thead><tr><th class="w-12 text-center">#</th><th>Customer</th><th>Points</th><th>Tier</th></tr></thead>
<tbody id="lb-table"><tr><td colspan="4" class="text-center text-surface-400 py-8">Loading...</td></tr></tbody></table></div></div>
<script>
fetch('/merchant/api/leaderboard').then(r=>r.json()).then(d=>{if(d.leaderboard){let h='';d.leaderboard.forEach((e,i)=>{let m=i==0?'🥇':i==1?'🥈':i==2?'🥉':(i+1);h+='<tr><td class=\"text-center text-lg\">'+m+'</td><td class=\"font-medium\">'+e.name+'</td><td class=\"font-bold text-bonus-600\">'+e.points.toLocaleString()+'</td><td><span class=\"badge-tier '+e.tier?.toLowerCase()+'\">'+e.tier+'</span></td></tr>';});document.getElementById('lb-table').innerHTML=h;}});
</script>
@endsection