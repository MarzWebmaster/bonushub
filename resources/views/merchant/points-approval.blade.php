@extends('layouts.app')

@section('title', 'Points Approval - BonusHub')

@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Points & Redemption Approval</h1>
            <p class="page-subtitle">Review and approve customer points and redemptions</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-surface-200 mb-6">
        <nav class="flex gap-6">
            <button onclick="switchTab('pending')" id="tab-pending" class="pb-3 text-sm font-medium border-b-2 border-bonus-500 text-bonus-600">Pending ({{ count($pendingApprovals ?? []) }})</button>
            <button onclick="switchTab('approved')" id="tab-approved" class="pb-3 text-sm font-medium text-surface-500 hover:text-surface-700 border-b-2 border-transparent">Approved</button>
            <button onclick="switchTab('rejected')" id="tab-rejected" class="pb-3 text-sm font-medium text-surface-500 hover:text-surface-700 border-b-2 border-transparent">Rejected</button>
        </nav>
    </div>

    {{-- Pending Tab --}}
    <div id="section-pending" class="space-y-4">
        @forelse($pendingApprovals ?? [] as $approval)
            <div class="card p-5">
                <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center text-lg shrink-0">
                            @if($approval->type === 'redeem' || $approval->is_redemption)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-surface-800">
                                <strong>{{ $approval->customer_name ?? $approval->customer->name ?? 'Customer' }}</strong>
                                @if($approval->type === 'redeem' || $approval->is_redemption)
                                    wants to redeem <strong class="text-orange-500">{{ $approval->reward_name ?? $approval->reward->name ?? 'an item' }}</strong>
                                @else
                                    earned <strong class="text-emerald-600">{{ number_format($approval->points ?? 0) }} points</strong>
                                @endif
                            </p>
                            <p class="text-xs text-surface-400 mt-1">{{ $approval->created_at ? $approval->created_at->diffForHumans() : '' }} | Staff: {{ $approval->staff_name ?? $approval->staff->name ?? 'System' }}</p>
                            @if($approval->notes)
                                <p class="text-sm text-surface-500 mt-2 italic">{{ $approval->notes }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('merchant.points-approval.approve', $approval->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ route('merchant.points-approval.reject', $approval->id) }}" class="inline" onsubmit="return confirm('Reject this request?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-surface-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-surface-400">No pending approvals. All caught up!</p>
            </div>
        @endforelse
    </div>

    {{-- Approved Tab --}}
    <div id="section-approved" class="space-y-4 hidden">
        @forelse($approvedApprovals ?? [] as $approval)
            <div class="card p-4 opacity-75">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-surface-600">
                            <strong>{{ $approval->customer_name ?? $approval->customer->name ?? 'Customer' }}</strong>
                            @if($approval->type === 'redeem' || $approval->is_redemption) redeemed <strong class="text-orange-500">{{ $approval->reward_name ?? 'item' }}</strong>
                            @else earned <strong class="text-emerald-600">{{ number_format($approval->points ?? 0) }} points</strong>
                            @endif
                        </p>
                        <p class="text-xs text-surface-400 mt-0.5">{{ $approval->created_at ? $approval->created_at->diffForHumans() : '' }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 shrink-0">Approved</span>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center">
                <p class="text-surface-400">No approved items yet.</p>
            </div>
        @endforelse
    </div>

    {{-- Rejected Tab --}}
    <div id="section-rejected" class="space-y-4 hidden">
        @forelse($rejectedApprovals ?? [] as $approval)
            <div class="card p-4 opacity-75">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-surface-600">
                            <strong>{{ $approval->customer_name ?? $approval->customer->name ?? 'Customer' }}</strong>
                            @if($approval->type === 'redeem' || $approval->is_redemption) redemption of <strong>{{ $approval->reward_name ?? 'item' }}</strong>
                            @else points award of <strong>{{ number_format($approval->points ?? 0) }}</strong>
                            @endif was rejected
                        </p>
                        <p class="text-xs text-surface-400 mt-0.5">{{ $approval->created_at ? $approval->created_at->diffForHumans() : '' }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600 shrink-0">Rejected</span>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center">
                <p class="text-surface-400">No rejected items.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
    ['pending', 'approved', 'rejected'].forEach(t => {
        document.getElementById('section-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab-' + t);
        if (t === tab) {
            btn.classList.add('border-bonus-500', 'text-bonus-600');
            btn.classList.remove('border-transparent', 'text-surface-500');
        } else {
            btn.classList.remove('border-bonus-500', 'text-bonus-600');
            btn.classList.add('border-transparent', 'text-surface-500');
        }
    });
}
</script>
@endpush
@endsection