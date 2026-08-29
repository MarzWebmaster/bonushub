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

    <!-- My Prizes -->
    <div class="card" style="margin-bottom:24px" x-show="prizes.length > 0">
        <div class="card-header">
            <h2 class="card-title">🏆 My Prizes</h2>
        </div>
        <div class="card-body" style="padding:0">
            <template x-for="prize in prizes" :key="prize.id">
                <div style="display:flex;align-items:center;gap:16px;padding:16px 24px;border-bottom:1px solid var(--border)">
                    <span style="font-size:32px">🏆</span>
                    <div style="flex:1">
                        <div style="font-weight:600;color:var(--text-primary)" x-text="prize.campaign?.title || 'Giveaway'"></div>
                        <div style="font-size:14px;color:var(--warning);font-weight:500" x-text="prize.prize_description"></div>
                        <div style="font-size:12px;color:var(--text-muted)" x-text="'Won: ' + new Date(prize.won_at).toLocaleDateString()"></div>
                    </div>
                    <span class="badge" :class="prize.status === 'claimed' ? 'badge-success' : 'badge-warning'" x-text="prize.status"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Active Giveaways -->
    <div class="card" style="margin-bottom:24px">
        <div class="card-header">
            <h2 class="card-title">🟢 Active Now</h2>
        </div>
        <div class="card-body">
            <template x-if="loading">
                <div class="empty-state">
                    <div class="empty-state-icon">⏳</div>
                    <p class="empty-state-text">Loading giveaways...</p>
                </div>
            </template>
            <template x-if="!loading && campaigns.length === 0">
                <div class="empty-state">
                    <div class="empty-state-icon">🎉</div>
                    <h3 class="empty-state-title">No active giveaways</h3>
                    <p class="empty-state-text">Check back later for new giveaways!</p>
                </div>
            </template>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
                <template x-for="campaign in campaigns" :key="campaign.id">
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:24px;cursor:pointer"
                         @click="window.location.href='/customer/giveaways/' + campaign.id">
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px">
                            <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:0" x-text="campaign.title"></h3>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <div style="background:var(--primary-light);border-radius:8px;padding:12px;text-align:center;margin-bottom:12px">
                            <div style="font-size:13px;color:var(--text-secondary);margin-bottom:4px">🏆 Prize</div>
                            <div style="font-size:16px;font-weight:700;color:var(--primary)" x-text="campaign.prize_description"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-secondary)">
                            <span x-text="'📝 ' + (campaign.entries_count || 0) + ' entries'"></span>
                            <span x-text="campaign.ends_at ? 'Ends: ' + new Date(campaign.ends_at).toLocaleDateString() : 'No end date'"></span>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:8px">
                            by <span x-text="campaign.merchant?.name || campaign.merchant?.company_name || 'Merchant'" style="font-weight:500"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function customerGiveaways() {
    return {
        campaigns: [],
        prizes: [],
        loading: true,

        async init() {
            await Promise.all([this.loadCampaigns(), this.loadPrizes()]);
            this.loading = false;
        },

        async loadCampaigns() {
            try {
                const res = await fetch('/customer/api/giveaways', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await res.json();
                if (data.success) this.campaigns = data.campaigns;
            } catch (e) { console.error(e); }
        },

        async loadPrizes() {
            try {
                const res = await fetch('/customer/api/my-prizes', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await res.json();
                if (data.success) this.prizes = data.prizes;
            } catch (e) { console.error(e); }
        }
    };
}
</script>
@endsection