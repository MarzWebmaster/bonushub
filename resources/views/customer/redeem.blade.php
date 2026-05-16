@extends('layouts.app')

@section('title', 'Redeem Points - BonusHub')
@section('page-title', 'Redeem Your Points')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Balance Card --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-indigo-200 text-sm">Your Balance</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($pointsBalance ?? 0) }} points</p>
            </div>
        </div>
    </div>

    {{-- Confirmation Form --}}
    @if(isset($reward) && $reward)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Confirm Redemption</h2>

            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-16 h-16 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-900">{{ $reward->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $reward->description ?? 'No description' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-bold text-indigo-600">{{ number_format($reward->points_required) }}</p>
                    <p class="text-xs text-gray-500">points</p>
                </div>
            </div>

            @if(($pointsBalance ?? 0) >= ($reward->points_required ?? 0))
                <form method="POST" action="{{ route('customer.redeem.confirm') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="reward_id" value="{{ $reward->id }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
                        <p class="text-sm text-gray-900">{{ $reward->shop_name ?? $reward->merchant->shop_name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-sm text-amber-800">You are about to redeem <strong>{{ $reward->name }}</strong> for <strong>{{ number_format($reward->points_required) }} points</strong>. This action cannot be undone.</p>
                        </div>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Confirm Redemption
                    </button>
                </form>
            @else
                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-700 text-center">You don't have enough points to redeem this reward. You need <strong>{{ number_format($reward->points_required - ($pointsBalance ?? 0)) }} more points</strong>.</p>
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('customer.rewards') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Browse other rewards</a>
                </div>
            @endif
        </div>
    @else
        {{-- Select from all rewards --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Select a Reward</h2>
            @if(isset($rewards) && count($rewards) > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($rewards as $reward)
                        <div class="py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $reward->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $reward->shop_name ?? '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-indigo-600">{{ number_format($reward->points_required) }} pts</span>
                                @if(($pointsBalance ?? 0) >= ($reward->points_required ?? 0))
                                    <a href="{{ route('customer.redeem', ['reward_id' => $reward->id]) }}" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition">Redeem</a>
                                @else
                                    <span class="text-xs text-gray-400">Need {{ number_format($reward->points_required - ($pointsBalance ?? 0)) }} more</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-4">No rewards available from your registered shops.</p>
            @endif
        </div>
    @endif

    {{-- Redemption History --}}
    @if(isset($redemptionHistory) && count($redemptionHistory) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Redemption History</h3>
            <div class="divide-y divide-gray-100">
                @foreach($redemptionHistory as $redemption)
                    <div class="py-2 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">{{ $redemption->reward_name ?? 'Reward' }}</p>
                            <p class="text-xs text-gray-400">{{ $redemption->created_at ? $redemption->created_at->diffForHumans() : '' }}</p>
                        </div>
                        <span class="text-sm text-amber-600 font-medium">-{{ number_format($redemption->points_spent ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
