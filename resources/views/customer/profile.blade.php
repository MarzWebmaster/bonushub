@extends('layouts.app')@section('title','Profile - Customer')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">My Profile</h1><p class="page-subtitle">Manage your account details</p></div></div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Account Info</h2>
<div class="space-y-4"><div><p class="text-sm text-surface-500">Name</p><p class="font-medium">{{ Auth::user()->name ?? auth()->user()?->name ?? '-' }}</p></div>
<div><p class="text-sm text-surface-500">Email</p><p>{{ Auth::user()->email ?? '-' }}</p></div>
<div><p class="text-sm text-surface-500">Phone</p><p>{{ auth()->user()?->phone ?? '-' }}</p></div>
<div><p class="text-sm text-surface-500">Member Since</p><p>{{ Auth::user()?->created_at?->format('d M Y') ?? '-' }}</p></div></div></div>
<div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Linked Merchants</h2>
<div id="linked-merchants">Loading...</div></div></div></div>
<script>
fetch('/customer/api/profile').then(r=>r.json()).then(d=>{if(d.merchants){let h='';d.merchants.forEach(m=>{h+='<div class=\"flex items-center justify-between py-2 border-b last:border-0\"><span>'+m.name+'</span><span class=\"text-sm font-bold text-bonus-600\">'+m.points+' pts</span></div>';});document.getElementById('linked-merchants').innerHTML=h;}});
</script>
@endsection