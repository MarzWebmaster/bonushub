@extends('layouts.app')

@section('title', 'Customer Lookup - BonusHub')
@section('page-title', 'Customer Lookup')

@section('content')
<div class="space-y-6">
    {{-- Search Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('staff.customer-lookup') }}" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search by Email, Phone, or Name</label>
                    <input type="text" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Enter customer email, phone number, or name..." class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @if(isset($customer) && $customer)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold uppercase">
                            {{ substr($customer->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ $customer->name }}</h2>
                            <p class="text-sm text-gray-600">{{ $customer->email }} @if($customer->phone) | {{ $customer->phone }} @endif</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-indigo-600">{{ number_format($customer->points_balance ?? $customer->current_points ?? 0) }}</p>
                        <p class="text-xs text-gray-500">Points Balance</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600">Lifetime Points</p>
                        <p class="text-xl font-bold text-green-600">{{ number_format($customer->lifetime_points ?? 0) }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600">Total Redemptions</p>
                        <p class="text-xl font-bold text-amber-600">{{ number_format($customer->total_redemptions ?? 0) }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600">Registered Shops</p>
                        <p class="text-xl font-bold text-blue-600">{{ number_format($customer->registered_shops ?? $customer->merchants_count ?? 1) }}</p>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('staff.add-points', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Points
                    </a>
                    <a href="{{ route('staff.redeem', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                        Redeem Points
                    </a>
                </div>

                {{-- Transaction History --}}
                <h3 class="text-sm font-semibold text-gray-900 mt-6 mb-3">Recent Transactions</h3>
                @if(isset($transactions) && count($transactions) > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($transactions as $txn)
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full
                                        @if($txn->type === 'earned' || $txn->type === 'points_added') bg-green-100 text-green-600
                                        @elseif($txn->type === 'redeemed') bg-amber-100 text-amber-600
                                        @else bg-red-100 text-red-600
                                        @endif flex items-center justify-center">
                                        @if($txn->type === 'earned' || $txn->type === 'points_added')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($txn->type === 'redeemed')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-800">{{ $txn->description ?? ucfirst(str_replace('_', ' ', $txn->type)) }}</p>
                                        <p class="text-xs text-gray-400">{{ $txn->created_at ? $txn->created_at->diffForHumans() : '' }}</p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold
                                    @if($txn->type === 'earned' || $txn->type === 'points_added') text-green-600
                                    @else text-red-600
                                    @endif">
                                    @if($txn->type === 'earned' || $txn->type === 'points_added') +@endif{{ number_format($txn->points ?? $txn->amount ?? 0) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No recent transactions.</p>
                @endif
            </div>
        </div>
    @elseif(request('search'))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-gray-600">No customer found matching <strong>"{{ request('search') }}"</strong></p>
            <p class="text-sm text-gray-400 mt-1">Try a different search term.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-gray-500">Enter a customer email, phone number, or name to look up their details.</p>
        </div>
    @endif
</div>
@endsection
