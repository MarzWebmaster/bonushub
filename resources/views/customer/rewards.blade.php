@extends('layouts.app')@section('title','Rewards - Customer')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">Available Rewards</h1><p class="page-subtitle">Redeem your points for rewards</p></div></div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="rewards-grid">Loading...</div></div>
<script>
fetch('/customer/api/rewards').then(r=>r.json()).then(d=>{if(d.rewards){let h='';d.rewards.forEach(r=>{h+='<div class=\"card p-4\"><h3 class=\"font-bold text-surface-800\">'+r.name+'</h3><p class=\"text-sm text-surface-500\">'+r.merchant+'</p><p class=\"text-2xl font-bold text-bonus-600 mt-2\">'+r.points_required+' pts</p><form action="{{ route('customer.redeem') }}" method="POST" class="mt-3">@csrf<input type="hidden" name="reward_id" value="'+r.id+'"><button type="submit" class="btn-primary w-full text-sm">Redeem</button></form></div>';});document.getElementById('rewards-grid').innerHTML=h;}});
</script>
@endsection