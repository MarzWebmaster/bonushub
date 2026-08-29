<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BonusHub — Loyalty System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        /* Mobile sidebar */
        @media (max-width: 1023px) {
            .sidebar-mobile { transform: translateX(-100%); position: fixed; z-index: 50; }
            .sidebar-mobile.open { transform: translateX(0); }
            .topbar-mobile { left: 0 !important; z-index: 50 !important; }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-surface-50 text-surface-800 antialiased font-sans">
    {{-- Dark mode body class --}}
    <div x-show="darkMode" x-cloak class="hidden"></div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div id="flash-message" class="fixed top-4 right-4 z-[60] bg-emerald-500 text-white px-5 py-3 rounded-xl shadow-lg shadow-emerald-500/20 flex items-center gap-2 animate-slide-down">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 hover:text-emerald-200">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div id="flash-message" class="fixed top-4 right-4 z-[60] bg-red-500 text-white px-5 py-3 rounded-xl shadow-lg shadow-red-500/20 flex items-center gap-2 animate-slide-down">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 hover:text-red-200">&times;</button>
        </div>
    @endif
    @if (session('warning'))
        <div id="flash-message" class="fixed top-4 right-4 z-[60] bg-amber-500 text-white px-5 py-3 rounded-xl shadow-lg shadow-amber-500/20 flex items-center gap-2 animate-slide-down">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('warning') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 hover:text-amber-200">&times;</button>
        </div>
    @endif

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-on:click="sidebarOpen = false"></div>

    {{-- ===== SIDEBAR ===== --}}
    <aside x-bind:class="sidebarOpen ? 'open' : ''" class="sidebar sidebar-mobile lg:!translate-x-0 flex flex-col">
        {{-- Logo --}}
        <div class="sidebar-logo">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-lg font-bold bg-gradient-to-r from-bonus-600 to-purple-600 bg-clip-text text-transparent">BonusHub</span>
        </div>

        {{-- User profile --}}
        <div class="px-4 py-4 border-b border-surface-100 dark:border-surface-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center text-sm font-bold text-white shadow-sm">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-surface-800 dark:text-white truncate">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-xs text-surface-500 dark:text-surface-400 truncate">
                        @role('superadmin') Super Admin
                        @elserole('merchant') Merchant
                        @elserole('staff') Staff
                        @elserole('customer') Customer
                        @endrole
                    </p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav flex-1">
            {{-- SUPERADMIN --}}
            @role('superadmin')
            <div class="sidebar-section">
                <p class="sidebar-section-title">Management</p>
            </div>
            <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link {{ request()->routeIs('superadmin.dashboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('superadmin.merchants') }}" class="sidebar-link {{ request()->routeIs('superadmin.merchants*') && !request()->routeIs('superadmin.merchants.pending*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Merchants</span>
            </a>
            <a href="{{ route('superadmin.merchants.pending') }}" class="sidebar-link {{ request()->routeIs('superadmin.merchants.pending*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Merchant Menunggu</span>
                <span class="sidebar-badge bg-amber-500" id="pending-merchant-count">0</span>
            </a>
            <a href="{{ route('superadmin.packages') }}" class="sidebar-link {{ request()->routeIs('superadmin.packages*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Packages</span>
            </a>
            <a href="{{ route('superadmin.leaderboard') }}" class="sidebar-link {{ request()->routeIs('superadmin.leaderboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Leaderboard</span>
            </a>
            <a href="{{ route('superadmin.audit') }}" class="sidebar-link {{ request()->routeIs('superadmin.audit*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Audit Trail</span>
            </a>
            <div class="sidebar-section">
                <p class="sidebar-section-title">System</p>
            </div>
            <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Settings</span>
            </a>
            @endrole

            {{-- MERCHANT --}}
            @role('merchant')
            <div class="sidebar-section">
                <p class="sidebar-section-title">Overview</p>
            </div>
            <a href="{{ route('merchant.dashboard') }}" class="sidebar-link {{ request()->routeIs('merchant.dashboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <div class="sidebar-section">
                <p class="sidebar-section-title">Loyalty</p>
            </div>
            <a href="{{ route('merchant.points.pending') }}" class="sidebar-link {{ request()->routeIs('merchant.points*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Point Approvals</span>
                <span class="sidebar-badge" id="pending-count">0</span>
            </a>
            <a href="{{ route('merchant.rewards.index') }}" class="sidebar-link {{ request()->routeIs('merchant.rewards*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                <span>Rewards</span>
            </a>
            <a href="{{ route('merchant.customers') }}" class="sidebar-link {{ request()->routeIs('merchant.customers*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM6 5v2m0 0v2m0-2h2m-2 0H4"/></svg>
                <span>Customers</span>
            </a>
            <div class="sidebar-section">
                <p class="sidebar-section-title">Engagement</p>
            </div>
            <a href="{{ route('merchant.leaderboard') }}" class="sidebar-link {{ request()->routeIs('merchant.leaderboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Leaderboard</span>
            </a>
            <a href="{{ route('merchant.loyalty.rates') }}" class="sidebar-link {{ request()->routeIs('merchant.loyalty*') && !request()->routeIs('merchant.leaderboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Loyalty Rates</span>
            </a>
            <a href="{{ route('merchant.tiers') }}" class="sidebar-link {{ request()->routeIs('merchant.tiers*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Tiers</span>
            </a>
            <a href="{{ route('merchant.campaigns') }}" class="sidebar-link {{ request()->routeIs('merchant.campaigns*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <span>Campaigns</span>
            </a>
            <div class="sidebar-section">
                <p class="sidebar-section-title">Reports</p>
            </div>
            <a href="{{ route('merchant.analytics') }}" class="sidebar-link {{ request()->routeIs('merchant.analytics') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Analytics</span>
            </a>
            <a href="{{ route('merchant.reports.liability') }}" class="sidebar-link {{ request()->routeIs('merchant.reports*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Reports</span>
            </a>
            <div class="sidebar-section">
                <p class="sidebar-section-title">Management</p>
            </div>
            <a href="{{ route('merchant.profile') }}" class="sidebar-link {{ request()->routeIs('merchant.profile*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Profil Syarikat</span>
            </a>
            <a href="{{ route('merchant.promos.index') }}" class="sidebar-link {{ request()->routeIs('merchant.promos*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Promos</span>
            </a>
            @endrole

            {{-- STAFF --}}
            @role('staff')
            <div class="sidebar-section">
                <p class="sidebar-section-title">Staff Tools</p>
            </div>
            <a href="{{ route('staff.dashboard') }}" class="sidebar-link {{ request()->routeIs('staff.dashboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Lookup Customer</span>
            </a>
            <a href="{{ route('staff.dashboard') }}?action=add" class="sidebar-link {{ request()->get('action') === 'add' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Add Points</span>
            </a>
            <a href="{{ route('staff.dashboard') }}?action=redeem" class="sidebar-link {{ request()->get('action') === 'redeem' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                <span>Redeem</span>
            </a>
            <a href="{{ route('staff.dashboard') }}?action=void" class="sidebar-link {{ request()->get('action') === 'void' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Void</span>
            </a>
            <a href="{{ route('staff.qr') }}" class="sidebar-link {{ request()->routeIs('staff.qr*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>QR Code</span>
            </a>
            @endrole

            {{-- CUSTOMER --}}
            @role('customer')
            <div class="sidebar-section">
                <p class="sidebar-section-title">My Account</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="sidebar-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('customer.points') }}" class="sidebar-link {{ request()->routeIs('customer.points*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>My Points</span>
            </a>
            <a href="{{ route('customer.rewards') }}" class="sidebar-link {{ request()->routeIs('customer.rewards*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                <span>Rewards</span>
            </a>
            <a href="{{ route('customer.merchants') }}" class="sidebar-link {{ request()->routeIs('customer.merchants*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Join Merchants</span>
            </a>
            <a href="{{ route('customer.leaderboard') }}" class="sidebar-link {{ request()->routeIs('customer.leaderboard*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Leaderboard</span>
            </a>
            <a href="{{ route('customer.profile') }}" class="sidebar-link {{ request()->routeIs('customer.profile*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profile</span>
            </a>
            @endrole
        </nav>

        {{-- Bottom: Dark Mode + Logout --}}
        <div class="px-3 py-4 border-t border-surface-200 dark:border-surface-700">
            <div class="flex items-center justify-between px-3 pb-3">
                <span class="text-xs text-surface-500 dark:text-surface-400">Dark Mode</span>
                <button x-on:click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="dark-toggle">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full !text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="main-content">
        <header class="topbar topbar-mobile">
            <div class="flex items-center gap-4">
                <button x-on:click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-surface-800 dark:text-white">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface-100 dark:bg-surface-700 text-surface-400 text-sm w-48">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-sm w-full text-surface-700 dark:text-surface-200 placeholder-surface-400">
                </div>
                <div class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200">
                    <span class="hidden sm:inline font-medium">{{ Auth::user()->name ?? 'User' }}</span>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white shadow-sm">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 overflow-y-auto">
            {{-- Validation Errors --}}
            @if (!empty($errors) && $errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-5 py-4 rounded-xl animate-fade-in">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-medium">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="px-6 py-3 border-t border-surface-200 dark:border-surface-700 text-center text-xs text-surface-400">
            &copy; {{ date('Y') }} BonusHub Loyalty System by <a href="https://marztechnology.com.my" target="_blank" class="text-bonus-500 hover:text-bonus-600 font-medium">Marz Technology</a>
        </footer>
    </div>

    <script>
        // Auto-dismiss flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('flash-message');
            if (flash) {
                setTimeout(() => {
                    flash.style.opacity = '0';
                    flash.style.transition = 'opacity 0.5s';
                    setTimeout(() => flash.remove(), 500);
                }, 5000);
            }
        });

        // Load pending approvals count (for merchant sidebar badge)
        @role('merchant')
        fetch('{{ route("merchant.api.points.pending") }}')
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    document.getElementById('pending-count').textContent = d.pending || 0;
                }
            })
            .catch(() => {});
        @endrole

        // Load pending merchant count (for superadmin sidebar badge)
        @role('superadmin')
        fetch('{{ route("superadmin.api.merchants.pending") }}')
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    const count = d.merchants ? d.merchants.length : 0;
                    const badge = document.getElementById('pending-merchant-count');
                    if (badge) {
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'inline-flex' : 'none';
                    }
                }
            })
            .catch(() => {});
        @endrole
    </script>
    @stack('scripts')
</body>
</html>
