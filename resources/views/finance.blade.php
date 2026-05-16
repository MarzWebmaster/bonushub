@extends('layouts.app')

@section('title', 'Finance - BonusHub')
@section('page-title', 'Finance')

@section('content')
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600 mt-1">RM {{ number_format($totalRevenue ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Pending Payouts</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">RM {{ number_format($pendingPayouts ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Active Subscriptions</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $activeSubscriptions ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">This Month</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">RM {{ number_format($thisMonthRevenue ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions ?? [] as $txn)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $txn->created_at ? $txn->created_at->format('d M Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $txn->description ?? 'Transaction' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($txn->type === 'subscription') bg-blue-100 text-blue-800
                                    @elseif($txn->type === 'payout') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">{{ ucfirst($txn->type ?? 'unknown') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold
                                @if(($txn->amount ?? 0) > 0) text-green-600 @else text-red-600 @endif">
                                RM {{ number_format(abs($txn->amount ?? 0), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
