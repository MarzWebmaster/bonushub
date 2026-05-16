@extends('layouts.app')
@section('title', 'Dashboard - Customer')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Dashboard</h1>
            <p class="page-subtitle">Your loyalty overview</p>
        </div>
    </div>
    <div class="stats-grid">
        <div class="stat-card border-l-bonus-500">
            <div><p class="text-sm font-medium text-surface-500">Total Points</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ number_format($totalPoints ?? 0) }}</p></div>
            <div class="w-12 h-12 rounded-xl bg-bonus-50 flex items-center justify-center"><svg class="w-6 h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-emerald-500">
            <div><p class="text-sm font-medium text-surface-500">Merchants</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ number_format($merchantCount ?? 0) }}</p></div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
        </div>
        <div class="stat-card border-l-amber-500">
            <div><p class="text-sm font-medium text-surface-500">My Tier</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ $tier ?? 'Basic' }}</p></div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-purple-500">
            <div><p class="text-sm font-medium text-surface-500">Available Rewards</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ number_format($availableRewards ?? 0) }}</p></div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
    </div>
</div>
@endsection