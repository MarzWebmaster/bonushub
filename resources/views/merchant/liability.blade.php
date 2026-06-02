@extends('layouts.app')
@section('title','Liability Report - Merchant')
@section('content')
<div class="page-container">
  <div class="page-header">
    <div>
      <h1 class="page-title">Points Liability Report</h1>
      <p class="page-subtitle">Track your points issued vs redeemed</p>
    </div>
  </div>

  <div id="liability-report" class="card p-6">Loading...</div>
</div>

<script>
fetch('/merchant/api/reports/liability').then(r=>r.json()).then(d=>{
  if(!d.success){ document.getElementById('liability-report').innerHTML='<p class="text-red-500">Failed to load report.</p>'; return; }
  const r = d.report;
  const outstanding = r.outstanding ?? r.total_points_outstanding ?? 0;
  const issued = r.total_issued ?? 0;
  const redeemed = r.total_redeemed ?? 0;
  const rate = r.redemption_rate ?? (issued > 0 ? Math.round(redeemed/issued*100) : 0);

  document.getElementById('liability-report').innerHTML = `
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="stat-card border-l-bonus-500">
        <p class="text-sm text-surface-500">Total Points Issued</p>
        <p class="text-2xl font-bold">${issued.toLocaleString()}</p>
      </div>
      <div class="stat-card border-l-red-500">
        <p class="text-sm text-surface-500">Total Points Redeemed</p>
        <p class="text-2xl font-bold">${redeemed.toLocaleString()}</p>
      </div>
      <div class="stat-card border-l-amber-500">
        <p class="text-sm text-surface-500">Outstanding Liability</p>
        <p class="text-2xl font-bold text-amber-600">${outstanding.toLocaleString()}</p>
      </div>
      <div class="stat-card border-l-emerald-500">
        <p class="text-sm text-surface-500">Redemption Rate</p>
        <p class="text-2xl font-bold text-emerald-600">${rate}%</p>
      </div>
    </div>
    <p class="text-xs text-surface-400 mt-4">Generated: ${r.generated_at ?? new Date().toISOString()}</p>
  `;
});
</script>
@endsection
