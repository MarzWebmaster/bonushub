@extends('layouts.app')

@section('title', 'Staff Dashboard - BonusHub')
@section('page-title', 'Staff Dashboard')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Customers Served Today</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $customersToday ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Points Awarded Today</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($pointsToday ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Pending Redemptions</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingRedemptions ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('staff.customer-lookup') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition text-center">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span class="text-xs font-medium text-gray-700">Customer Lookup</span>
                </a>
                <a href="{{ route('staff.add-points') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition text-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="text-xs font-medium text-gray-700">Add Points</span>
                </a>
                <a href="{{ route('staff.redeem') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition text-center">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                    <span class="text-xs font-medium text-gray-700">Redeem</span>
                </a>
                <a href="{{ route('staff.void') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 transition text-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs font-medium text-gray-700">Void</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
            @if(isset($recentActivity) && count($recentActivity) > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($recentActivity as $activity)
                        <div class="py-2 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-700">{{ $activity->description ?? 'Activity' }}</p>
                                <p class="text-xs text-gray-400">{{ $activity->created_at ? $activity->created_at->diffForHumans() : '' }}</p>
                            </div>
                            <span class="text-xs font-medium
                                @if(($activity->points ?? 0) > 0) text-green-600
                                @else text-red-600
                                @endif">{{ ($activity->points ?? 0) > 0 ? '+' : '' }}{{ number_format($activity->points ?? 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No recent activity.</p>
            @endif
        </div>
    </div>
</div>
@endsection
