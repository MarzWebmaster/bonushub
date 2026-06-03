@extends('layouts.app')
@section('title', 'Dashboard - Merchant')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Merchant Dashboard</h1>
            <p class="page-subtitle">Your shop at a glance</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card border-l-bonus-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Total Customers</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1">{{ number_format($stats['total_customers'] ?? 0) }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-bonus-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-emerald-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Points Awarded</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1">{{ number_format($stats['total_points'] ?? 0) }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-amber-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Pending Approvals</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1">{{ number_format($stats['pending_approvals'] ?? 0) }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-purple-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Reward Products</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1">{{ number_format($stats['total_products'] ?? 0) }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
    </div>

    <div class="card mt-6 p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-bold text-surface-800 mb-4">Customer & Points Overview (6 Months)</h2>
        <div class="relative" style="height:280px"><canvas id="overview-chart"></canvas></div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
        <a href="{{ route('merchant.customers') }}" class="card p-4 text-center hover:bg-bonus-50 transition cursor-pointer">
            <svg class="w-6 h-6 mx-auto text-bonus-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm font-medium text-surface-700">Customers</p>
        </a>
        <a href="{{ route('merchant.points.pending') }}" class="card p-4 text-center hover:bg-amber-50 transition cursor-pointer">
            <svg class="w-6 h-6 mx-auto text-amber-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-surface-700">Approvals</p>
        </a>
        <a href="{{ route('merchant.rewards.index') }}" class="card p-4 text-center hover:bg-purple-50 transition cursor-pointer">
            <svg class="w-6 h-6 mx-auto text-purple-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-medium text-surface-700">Rewards</p>
        </a>
        <a href="{{ route('merchant.tiers') }}" class="card p-4 text-center hover:bg-emerald-50 transition cursor-pointer">
            <svg class="w-6 h-6 mx-auto text-emerald-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-surface-700">Tiers</p>
        </a>
    </div>
</div>

<script>
fetch('/merchant/api/dashboard').then(r => r.json()).then(d => {
    if (!d.success) return;
    const c = d.chart;
    const ctx = document.getElementById('overview-chart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: c.labels,
            datasets: [
                { label: 'Registrations', data: c.registrations, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', tension: 0.3, fill: false },
                { label: 'Points Earned', data: c.earned, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.3, fill: false },
                { label: 'Points Redeemed', data: c.redeemed, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', tension: 0.3, fill: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endsection
