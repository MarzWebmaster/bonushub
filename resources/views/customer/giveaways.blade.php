@extends('layouts.app')
@section('title', 'Giveaways')
@section('content')
<div class="page-container" style="padding-top:0" x-data="customerGiveaways()">
    <div class="page-header">
        <div>
            <h1 class="page-title">🎉 Active Giveaways</h1>
            <p class="page-subtitle">Enter giveaways and win prizes!</p>
        </div>
    </div>

    {{-- My Prizes --}}
    <div class="card overflow-hidden mb-6" x-show="prizes.length > 0">
        <div class="px-4 pt-4 pb-2"><h2 class="font-semibold text-sm">🏆 My Prizes</h2></div>
        <template x-for="prize in prizes" :key="prize.id">
            <div class="flex items-center gap-3 px-4 py-3 border-t border-surface-100">
                <span class="text-2xl flex-shrink-0">🏆</span>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate" x-text="prize.campaign?.title || 'Giveaway'"></div>
                    <div class="text-sm text-amber-600 font-medium" x-text="prize.prize_description"></div>
                    <div class="text-xs text-surface-400" x-text="'Won: ' + new Date(prize.won_at).toLocaleDateString()"></div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0" :class="prize.status === 'claimed' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'" x-text="prize.status"></span>
            </div>
        </template>
    </div>

    {{-- Active Giveaways --}}
    <div class="card p-4 sm:p-5">
        <h2 class="font-semibold text-sm mb-4">🟢 Active Now</h2>

        <template x-if="loading">
            <div class="text-center py-12 text-surface-400">
                <svg class="w-8 h-8 animate-spin mx-auto mb-2 text-bonus-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Loading giveaways...
            </div>
        </template>
        <template x-if="!loading && campaigns.length === 0">
            <div class="text-center py-12 text-surface-400">
                <div class="text-5xl mb-3">🎉</div>
                <h3 class="font-semibold text-surface-600 mb-1">No active giveaways</h3>
                <p class="text-sm">Check back later for new giveaways!</p>
            </div>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="campaign in campaigns" :key="campaign.id">
                <div class="border border-surface-200 rounded-xl p-4 hover:shadow-md transition-shadow cursor-pointer"
                     @click="window.location.href='/customer/giveaways/' + campaign.id">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-base leading-tight pr-2" x-text="campaign.title"></h3>
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full flex-shrink-0">Active</span>
                    </div>
                    <div class="bg-bonus-50 rounded-lg p-3 text-center mb-3">
                        <div class="text-xs text-surface-500 mb-0.5">🏆 Prize</div>
                        <div class="text-sm font-bold text-bonus-700" x-text="campaign.prize_description"></div>
                    </div>
                    <div class="flex justify-between text-xs text-surface-500">
                        <span x-text="'📝 ' + (campaign.entries_count || 0) + ' entries'"></span>
                        <span x-text="campaign.ends_at ? 'Ends: ' + new Date(campaign.ends_at).toLocaleDateString() : 'No end'"></span>
                    </div>
                    <div class="text-xs text-surface-400 mt-2">
                        by <span class="font-medium" x-text="campaign.merchant?.name || campaign.merchant?.company_name || 'Merchant'"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function customerGiveaways() {
    return {
        campaigns: [], prizes: [], loading: true,
        async init() {
            await Promise.all([this.loadCampaigns(), this.loadPrizes()]);
            this.loading = false;
        },
        async loadCampaigns() {
            try {
                const res = await fetch('/customer/api/giveaways', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const data = await res.json(); if (data.success) this.campaigns = data.campaigns;
            } catch (e) { console.error(e); }
        },
        async loadPrizes() {
            try {
                const res = await fetch('/customer/api/my-prizes', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const data = await res.json(); if (data.success) this.prizes = data.prizes;
            } catch (e) { console.error(e); }
        }
    };
}
</script>
@endsection
