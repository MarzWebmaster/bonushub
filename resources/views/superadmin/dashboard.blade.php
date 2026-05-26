@extends('layouts.app')
@section('title', 'Dashboard - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Overview of your BonusHub platform</p>
        </div>
        <div class="text-sm text-surface-400" id="last-updated"></div>
    </div>

    <div class="stats-grid" id="stats-container">
        <div class="stat-card border-l-bonus-500 hover:border-l-bonus-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-surface-500">Total Merchants</p>
                    <p class="text-3xl font-bold text-surface-800 mt-1" id="stat-merchants">—</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-bonus-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-emerald-500 hover:border-l-emerald-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-surface-500">Active Merchants</p>
                    <p class="text-3xl font-bold text-surface-800 mt-1" id="stat-active">—</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-blue-500 hover:border-l-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-surface-500">Total Customers</p>
                    <p class="text-3xl font-bold text-surface-800 mt-1" id="stat-customers">—</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-amber-500 hover:border-l-amber-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-surface-500">Pending Approvals</p>
                    <p class="text-3xl font-bold text-surface-800 mt-1" id="stat-pending">—</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-purple-500 hover:border-l-purple-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-surface-500">Total Staff</p>
                    <p class="text-3xl font-bold text-surface-800 mt-1" id="stat-staff">—</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card border-l-pink-500 hover:border-l-pink-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-surface-500">Packages</p>
                    <p class="text-3xl font-bold text-surface-800 mt-1" id="stat-packages">—</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-pink-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
        <a href="{{ route('superadmin.merchants') }}" class="card card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h3 class="font-semibold text-surface-800">Manage Merchants</h3>
                <p class="text-sm text-surface-500">View, approve, and manage all merchants</p>
            </div>
        </a>
        <a href="{{ route('superadmin.packages') }}" class="card card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <h3 class="font-semibold text-surface-800">Manage Packages</h3>
                <p class="text-sm text-surface-500">Configure subscription plans and features</p>
            </div>
        </a>
        <a href="{{ route('superadmin.leaderboard') }}" class="card card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <h3 class="font-semibold text-surface-800">View Leaderboard</h3>
                <p class="text-sm text-surface-500">See top customers across all merchants</p>
            </div>
        </a>
    </div>
</div>

<script>
fetch('{{ route("superadmin.dashboard.stats") }}')
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            document.getElementById('stat-merchants').textContent = d.stats.total_merchants.toLocaleString();
            document.getElementById('stat-active').textContent = d.stats.active_merchants.toLocaleString();
            document.getElementById('stat-customers').textContent = d.stats.total_customers.toLocaleString();
            document.getElementById('stat-pending').textContent = d.stats.pending_approvals.toLocaleString();
            document.getElementById('stat-staff').textContent = d.stats.total_staff.toLocaleString();
            document.getElementById('stat-packages').textContent = d.stats.total_packages.toLocaleString();
            document.getElementById('last-updated').textContent = 'Updated: ' + new Date(d.stats.generated_at).toLocaleTimeString();
        }
    })
    .catch(() => {
        document.querySelectorAll('#stats-container [id^=stat-]').forEach(el => el.textContent = '0');
    });
</script>
@endsection