@extends('layouts.app')

@section('title', 'Reports - BonusHub')
@section('page-title', 'Reports & Analytics')

@section('content')
<div class="space-y-6">
    {{-- Date Range Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('merchant.reports') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Apply</button>
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Period Points Awarded</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($periodPointsAwarded ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Period Redemptions</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($periodRedemptions ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Active Customers</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $activeCustomers ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Avg Points per Customer</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($avgPointsPerCustomer ?? 0) }}</p>
        </div>
    </div>

    {{-- Top Rewards --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Most Redeemed Rewards</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Times Redeemed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Points Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Remaining</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topRewards ?? [] as $reward)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $reward->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($reward->times_redeemed ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($reward->total_points_spent ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $reward->stock ?? '∞' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily Trend Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daily Activity</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points Awarded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Redemptions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Customers</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailyActivity ?? [] as $day)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $day->date ?? $day->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ number_format($day->points_awarded ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-600 font-medium">{{ number_format($day->redemptions ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($day->new_customers ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No daily data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Export Button --}}
    <div class="flex justify-end">
        <a href="{{ route('merchant.reports.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Report
        </a>
    </div>
</div>
@endsection
