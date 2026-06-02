@extends('layouts.app')
@section('title','Profile - Customer')
@section('content')
<div class="page-container">
  <div class="page-header">
    <div>
      <h1 class="page-title">My Profile</h1>
      <p class="page-subtitle">Manage your account details</p>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Account Info -->
    <div class="card p-6">
      <h2 class="font-bold text-surface-800 mb-4">Account Info</h2>
      <div class="space-y-4">
        <div>
          <p class="text-sm text-surface-500">Name</p>
          <p class="font-medium">{{ Auth::user()->name ?? '-' }}</p>
        </div>
        <div>
          <p class="text-sm text-surface-500">Email</p>
          <p>{{ Auth::user()->email ?? '-' }}</p>
        </div>
        <div>
          <p class="text-sm text-surface-500">Phone</p>
          <p>{{ auth()->user()?->phone ?? '-' }}</p>
        </div>
        <div>
          <p class="text-sm text-surface-500">Member Since</p>
          <p>{{ Auth::user()?->created_at?->format('d M Y') ?? '-' }}</p>
        </div>
      </div>
    </div>

    <!-- Linked Merchants -->
    <div class="card p-6">
      <h2 class="font-bold text-surface-800 mb-4">Linked Merchants</h2>
      <div id="linked-merchants">Loading...</div>
    </div>
  </div>
</div>

<script>
fetch('/customer/api/profile').then(r=>r.json()).then(d=>{
  const el = document.getElementById('linked-merchants');
  if(!d.success){ el.innerHTML='<p class="text-red-500">Failed to load profile.</p>'; return; }

  const merchants = d.customer?.customer_merchant || [];
  if(merchants.length){
    el.innerHTML = merchants.map(m => `
      <div class="flex items-center justify-between py-2 border-b last:border-0">
        <span>${m.merchant?.company_name ?? m.company_name ?? 'Unknown'}</span>
        <span class="text-sm font-bold text-bonus-600">${parseFloat(m.points).toLocaleString()} pts</span>
      </div>
    `).join('');
  } else {
    el.innerHTML = '<p class="text-surface-400 text-sm">No linked merchants yet.</p>';
  }
});
</script>
@endsection
