@extends('layouts.app')
@section('title', 'Dashboard - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Merchant Dashboard</h1>
            <p class="page-subtitle">Your shop at a glance</p>
        </div>
    </div>
    <div class="stats-grid">
        <div class="stat-card border-l-bonus-500">
            <div><p class="text-sm font-medium text-surface-500">Total Customers</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-total-customers">—</p></div>
            <div class="w-12 h-12 rounded-xl bg-bonus-50 flex items-center justify-center"><svg class="w-6 h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-emerald-500">
            <div><p class="text-sm font-medium text-surface-500">Points Awarded</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-points">—</p></div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-amber-500">
            <div><p class="text-sm font-medium text-surface-500">Pending Approvals</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-pending">—</p></div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-purple-500">
            <div><p class="text-sm font-medium text-surface-500">Products</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-products">—</p></div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
    </div>
</div>
<script>fetch('/merchant/api/dashboard').then(r=>r.json()).then(d=>{if(d.success){document.getElementById('m-total-customers').textContent=d.total_customers;document.getElementById('m-points').textContent=d.total_points;document.getElementById('m-pending').textContent=d.pending_approvals;document.getElementById('m-products').textContent=d.total_products;}});</script>
@endsection