@extends('layouts.app')@section('title','Leaderboard - Customer')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">Leaderboard</h1><p class="page-subtitle">See where you rank</p></div></div>
<div class="card overflow-hidden"><table class="data-table"><thead><tr><th class="w-12 text-center">#</th><th>Customer</th><th>Points</th><th>Tier</th></tr></thead>
<tbody id="lb-table"><tr><td colspan="4" class="text-center text-surface-400 py-8">Loading...</td></tr></tbody></table></div></div>
<script>
fetch('/customer/api/leaderboard').then(r=>r.json()).then(d=>{if(d.leaderboard){let h='';d.leaderboard.forEach((e,i)=>{let cls=i<3?'font-bold':'';let isMe=e.is_me?' bg-bonus-50 border-l-4 border-bonus-500':'';let m=i==0?'🥇':i==1?'🥈':i==2?'🥉':(i+1);h+='<tr class=\"'+isMe+'\"><td class=\"text-center text-lg '+cls+'\">'+m+'</td><td class=\"font-medium '+cls+'\">'+e.name+(e.is_me?' <span class=\"text-xs text-bonus-600\">(You)</span>':'')+'</td><td class=\"font-bold text-bonus-600\">'+e.points.toLocaleString()+'</td><td><span class=\"badge-tier '+e.tier?.toLowerCase()+'\">'+e.tier+'</span></td></tr>';});document.getElementById('lb-table').innerHTML=h;}});
</script>
@endsection