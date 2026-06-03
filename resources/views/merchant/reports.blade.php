@extends('layouts.app')

@section('title', 'Reports - BonusHub')

@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">📊 Reports & Analytics</h1>
            <p class="page-subtitle">Track points, redemptions, and customer activity</p>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('merchant.reports.liability') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="rounded-lg border border-surface-200 px-3 py-2 text-sm focus:ring-2 focus:ring-bonus-500 focus:border-bonus-500 bg-surface-50">
            </div>
            <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}" class="rounded-lg border border-surface-200 px-3 py-2 text-sm focus:ring-2 focus:ring-bonus-500 focus:border-bonus-500 bg-surface-50">
            </div>
            <button type="submit" class="px-4 py-2 bg-bonus-500 text-white text-sm font-medium rounded-lg hover:bg-bonus-600 transition">Apply</button>
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-5 text-center">
            <p class="text-sm font-medium text-surface-500">Points Awarded (Period)</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($periodPointsAwarded ?? 0) }}</p>
        </div>
        <div class="card p-5 text-center">
            <p class="text-sm font-medium text-surface-500">Redemptions (Period)</p>
            <p class="text-2xl font-bold text-orange-500 mt-1">{{ number_format($periodRedemptions ?? 0) }}</p>
        </div>
        <div class="card p-5 text-center">
            <p class="text-sm font-medium text-surface-500">Active Customers</p>
            <p class="text-2xl font-bold text-bonus-600 mt-1">{{ $activeCustomers ?? 0 }}</p>
        </div>
        <div class="card p-5 text-center">
            <p class="text-sm font-medium text-surface-500">Avg Points / Customer</p>
            <p class="text-2xl font-bold text-bonus-600 mt-1">{{ number_format($avgPointsPerCustomer ?? 0) }}</p>
        </div>
    </div>

    {{-- Top Rewards --}}
    <div class="card overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-surface-200">
            <h2 class="text-lg font-bold text-surface-800">Most Redeemed Rewards</h2>
        </div>
        <div class="overflow-x-auto"><table class="data-table">
            <thead>
                <tr>
                    <th>Reward</th>
                    <th>Times Redeemed</th>
                    <th>Total Points Spent</th>
                    <th>Stock Left</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topRewards ?? [] as $reward)
                    <tr class="hover:bg-surface-50">
                        <td class="font-medium text-surface-800">{{ $reward->name }}</td>
                        <td class="text-surface-600">{{ number_format($reward->times_redeemed ?? 0) }}</td>
                        <td class="text-surface-600">{{ number_format($reward->total_points_spent ?? 0) }}</td>
                        <td class="text-surface-600">{{ $reward->stock ?? '∞' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-surface-400 py-8">No data available.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    {{-- Daily Activity --}}
    <div class="card overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-surface-200">
            <h2 class="text-lg font-bold text-surface-800">Daily Activity</h2>
        </div>
        <div class="overflow-x-auto"><table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Points Awarded</th>
                    <th>Redemptions</th>
                    <th>New Customers</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyActivity ?? [] as $day)
                    <tr class="hover:bg-surface-50">
                        <td class="text-surface-800">{{ $day->date ?? $day->created_at->format('d M Y') }}</td>
                        <td class="text-emerald-600 font-medium">{{ number_format($day->points_awarded ?? 0) }}</td>
                        <td class="text-orange-500 font-medium">{{ number_format($day->redemptions ?? 0) }}</td>
                        <td class="text-bonus-600 font-medium">{{ number_format($day->new_customers ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-surface-400 py-8">No daily data available.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    {{-- Footer --}}
    <p class="text-xs text-surface-400 text-right">Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
</div>
@endsection