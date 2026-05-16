@extends('layouts.app')@section('title','My Points - Customer')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">My Points</h1><p class="page-subtitle">Track your earnings across merchants</p></div></div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<div class="lg:col-span-2"><div class="card overflow-hidden"><div class="p-4 border-b bg-surface-50 font-semibold text-surface-700">Points History</div>
<table class="data-table"><thead><tr><th>Merchant</th><th>Points</th><th>Type</th><th>Date</th></tr></thead>
<tbody id="pt-table"><tr><td colspan="4" class="text-center text-surface-400 py-8">Loading...</td></tr></tbody></table></div></div>
<div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Points Summary</h2><div id="pt-summary">Loading...</div></div></div></div>
<script>
fetch('/customer/api/points').then(r=>r.json()).then(d=>{if(d.success){let h='';d.history.forEach(t=>{h+='<tr><td>'+t.merchant+'</td><td class=\"font-bold '+(t.type=='earn'?'text-emerald-600':'text-red-500')+'\">'+(t.type=='earn'?'+':'-')+t.points+'</td><td>'+t.type+'</td><td class=\"text-surface-400 text-xs\">'+t.created_at+'</td></tr>';});document.getElementById('pt-table').innerHTML=h;let s='<div class=\"space-y-3\"><div><p class=\"text-sm text-surface-500\">Total Points</p><p class=\"text-3xl font-bold text-bonus-600\">'+d.total_points.toLocaleString()+'</p></div><div><p class=\"text-sm text-surface-500\">Merchants</p><p class=\"text-lg font-semibold\">'+d.merchant_count+'</p></div><div><p class=\"text-sm text-surface-500\">Tier</p><p class=\"text-lg font-semibold\">'+d.tier+'</p></div></div>';document.getElementById('pt-summary').innerHTML=s;}});
</script>
@endsection