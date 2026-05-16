@extends('layouts.app')

@section('title', 'Void Transaction - BonusHub')
@section('page-title', 'Void Transaction')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Void a Transaction</h2>
        <p class="text-sm text-gray-500 mb-6">Search for a transaction to void. This action is irreversible and will be logged in the audit trail.</p>

        {{-- Search Transaction --}}
        <form method="GET" action="{{ route('staff.void') }}" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search by Transaction ID or Customer</label>
                <div class="flex gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Enter transaction ID, customer name, or email..." class="flex-1 rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Search</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @if(isset($transactions) && count($transactions) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Search Results</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($transactions as $txn)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full
                                    @if($txn->type === 'earned' || $txn->type === 'points_added') bg-green-100 text-green-600
                                    @elseif($txn->type === 'redeemed') bg-amber-100 text-amber-600
                                    @else bg-gray-100 text-gray-600
                                    @endif flex items-center justify-center">
                                    @if($txn->type === 'earned' || $txn->type === 'points_added')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    @elseif($txn->type === 'redeemed')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($txn->type === 'earned' || $txn->type === 'points_added')
                                            Points awarded
                                        @elseif($txn->type === 'redeemed')
                                            Redemption
                                        @else
                                            {{ ucfirst($txn->type) }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">Customer: {{ $txn->customer_name ?? $txn->customer->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-400">{{ $txn->created_at ? $txn->created_at->format('d M Y H:i') : '' }} | #{{ $txn->id }}</p>
                                    @if($txn->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $txn->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold
                                    @if($txn->type === 'earned' || $txn->type === 'points_added') text-green-600
                                    @else text-amber-600
                                    @endif">
                                    {{ number_format($txn->points ?? $txn->amount ?? 0) }} pts
                                </span>
                                @if($txn->is_voided ?? false)
                                    <p class="text-xs text-red-600 mt-1">Already voided</p>
                                @else
                                    <form method="POST" action="{{ route('staff.void.submit', $txn->id) }}" class="mt-2" onsubmit="return confirm('Are you sure you want to void this transaction? This cannot be undone.')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition">Void</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(request('search'))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-gray-600">No transactions found matching <strong>"{{ request('search') }}"</strong></p>
        </div>
    @endif

    {{-- Recent Voided Transactions --}}
    @if(isset($voidedTransactions) && count($voidedTransactions) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Recently Voided</h3>
            <div class="divide-y divide-gray-100">
                @foreach($voidedTransactions as $void)
                    <div class="py-2 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">{{ $void->customer_name ?? $void->customer->name ?? 'Customer' }}</p>
                            <p class="text-xs text-gray-400">{{ $void->created_at ? $void->created_at->diffForHumans() : '' }}</p>
                        </div>
                        <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">Voided</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
