@extends('layouts.app')
@section('title', 'Analytics — Merchant')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">📊 Analytics</h1>
            <p class="page-subtitle">Performance overview for your business</p>
        </div>
    </div>

    {{-- TODAY'S SUMMARY --}}
    <h2 class="text-sm font-bold text-surface-500 uppercase tracking-wider mb-3">Today</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        <div class="card p-4 border-l-4 border-primary">
            <p class="text-xs font-medium text-surface-500">Today's Revenue</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">RM {{ number_format($todayRevenue, 2) }}</p>
        </div>
        <div class="card p-4 border-l-4 border-emerald-500">
            <p class="text-xs font-medium text-surface-500">Points Earned</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">{{ number_format($todayEarned) }}</p>
        </div>
        <div class="card p-4 border-l-4 border-amber-500">
            <p class="text-xs font-medium text-surface-500">Transactions</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">{{ number_format($todayTransactions) }}</p>
        </div>
    </div>

    {{-- ALL TIME SUMMARY --}}
    <h2 class="text-sm font-bold text-surface-500 uppercase tracking-wider mb-3">All Time</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        <div class="card p-4 border-l-4 border-bonus-500">
            <p class="text-xs font-medium text-surface-500">Customers</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">{{ number_format($totalCustomers) }}</p>
        </div>
        <div class="card p-4 border-l-4 border-emerald-500">
            <p class="text-xs font-medium text-surface-500">Total Revenue</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">RM {{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="card p-4 border-l-4 border-primary">
            <p class="text-xs font-medium text-surface-500">Points Earned</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">{{ number_format($totalPointsEarned) }}</p>
        </div>
        <div class="card p-4 border-l-4 border-red-500">
            <p class="text-xs font-medium text-surface-500">Points Redeemed</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">{{ number_format($totalPointsRedeemed) }}</p>
        </div>
        <div class="card p-4 border-l-4 border-purple-500">
            <p class="text-xs font-medium text-surface-500">Total Transactions</p>
            <p class="text-xl sm:text-2xl font-bold text-surface-800 mt-1">{{ number_format($totalTransactions) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- TOP CUSTOMERS --}}
        <div class="card p-4 sm:p-6">
            <h2 class="text-base font-bold text-surface-800 mb-4">🏆 Top Customers</h2>
            @if($topCustomers->isEmpty())
                <p class="text-sm text-surface-500 text-center py-8">No customers yet</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-surface-200">
                            <th class="text-left py-2 font-medium text-surface-500">#</th>
                            <th class="text-left py-2 font-medium text-surface-500">Name</th>
                            <th class="text-right py-2 font-medium text-surface-500">Points</th>
                            <th class="text-right py-2 font-medium text-surface-500 hidden sm:table-cell">Tier</th>
                            <th class="text-right py-2 font-medium text-surface-500 hidden sm:table-cell">Txns</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $i => $c)
                        <tr class="border-b border-surface-100 hover:bg-surface-50">
                            <td class="py-2 text-surface-400">{{ $i + 1 }}</td>
                            <td class="py-2 font-medium text-surface-800">{{ $c['name'] }}</td>
                            <td class="py-2 text-right font-semibold text-primary">{{ number_format($c['points']) }}</td>
                            <td class="py-2 text-right hidden sm:table-cell">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $c['tier'] === 'Gold' ? 'bg-yellow-100 text-yellow-700' : ($c['tier'] === 'Silver' ? 'bg-gray-100 text-gray-700' : 'bg-orange-100 text-orange-700') }}">
                                    {{ $c['tier'] }}
                                </span>
                            </td>
                            <td class="py-2 text-right text-surface-600 hidden sm:table-cell">{{ $c['transactions'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- BRANCH PERFORMANCE --}}
        <div class="card p-4 sm:p-6">
            <h2 class="text-base font-bold text-surface-800 mb-4">📍 Branch Performance</h2>
            @if($branchStats->isEmpty())
                <p class="text-sm text-surface-500 text-center py-8">No branch data yet</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-surface-200">
                            <th class="text-left py-2 font-medium text-surface-500">Branch</th>
                            <th class="text-right py-2 font-medium text-surface-500">Revenue</th>
                            <th class="text-right py-2 font-medium text-surface-500 hidden sm:table-cell">Points</th>
                            <th class="text-right py-2 font-medium text-surface-500 hidden sm:table-cell">Txns</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchStats as $b)
                        <tr class="border-b border-surface-100 hover:bg-surface-50">
                            <td class="py-2 font-medium text-surface-800">{{ $b['name'] }}</td>
                            <td class="py-2 text-right font-semibold text-emerald-600">RM {{ number_format($b['revenue'], 2) }}</td>
                            <td class="py-2 text-right text-primary hidden sm:table-cell">{{ number_format($b['points']) }}</td>
                            <td class="py-2 text-right text-surface-600 hidden sm:table-cell">{{ $b['transactions'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- RECENT TRANSACTIONS --}}
    <div class="card p-4 sm:p-6 mt-6">
        <h2 class="text-base font-bold text-surface-800 mb-4">📋 Recent Transactions</h2>
        @if($recentTransactions->isEmpty())
            <p class="text-sm text-surface-500 text-center py-8">No transactions yet</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-200">
                        <th class="text-left py-2 font-medium text-surface-500">Date</th>
                        <th class="text-left py-2 font-medium text-surface-500">Customer</th>
                        <th class="text-left py-2 font-medium text-surface-500 hidden sm:table-cell">Type</th>
                        <th class="text-right py-2 font-medium text-surface-500">Points</th>
                        <th class="text-right py-2 font-medium text-surface-500 hidden md:table-cell">Amount</th>
                        <th class="text-right py-2 font-medium text-surface-500 hidden lg:table-cell">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                    <tr class="border-b border-surface-100 hover:bg-surface-50">
                        <td class="py-2 text-surface-600 whitespace-nowrap">{{ $tx->created_at->format('d M Y') }}</td>
                        <td class="py-2 font-medium text-surface-800">{{ $tx->customer->name ?? 'N/A' }}</td>
                        <td class="py-2 hidden sm:table-cell">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $tx->type === 'earn' ? 'bg-emerald-100 text-emerald-700' : ($tx->type === 'redeem' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ ucfirst($tx->type) }}
                            </span>
                        </td>
                        <td class="py-2 text-right font-semibold {{ $tx->points > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                        </td>
                        <td class="py-2 text-right text-surface-600 hidden md:table-cell">
                            {{ $tx->amount_spent > 0 ? 'RM ' . number_format($tx->amount_spent, 2) : '-' }}
                        </td>
                        <td class="py-2 text-right hidden lg:table-cell">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $tx->status === 'approved' ? 'bg-green-100 text-green-700' : ($tx->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $tx->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
