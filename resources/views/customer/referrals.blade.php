@extends('layouts.app')
@section('title', 'My Referral Links')
@section('content')
<style>
.ref-stat{display:flex;flex-direction:column;align-items:center;padding:10px 6px;border-radius:10px;text-align:center}
.ref-stat .num{font-size:20px;font-weight:700;line-height:1.2}
.ref-stat .lbl{font-size:11px;color:#6b7280;margin-top:2px}
</style>
<div class="page-container" style="padding-top:0" x-data="referralManager()">
    <div class="page-header">
        <div>
            <h1 class="page-title">🔗 My Referral Links</h1>
            <p class="page-subtitle">Share with friends and earn points for each signup!</p>
        </div>
    </div>

    {{-- Create New Link --}}
    <div class="border-2 border-dashed border-bonus-400 rounded-xl p-4 sm:p-5 mb-5 bg-white">
        <h3 class="font-semibold text-sm mb-3">✨ Create Referral Link</h3>
        <div class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1 min-w-0">
                <label class="text-xs text-surface-500 mb-1 block">Business</label>
                <select x-model="newMerchantId" class="form-input w-full">
                    <option value="">Select business...</option>
                    @foreach(\App\Models\Merchant::where('status','approved')->get() as $m)
                    <option value="{{ $m->id }}">{{ $m->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-0 sm:max-w-[180px]">
                <label class="text-xs text-surface-500 mb-1 block">Source (optional)</label>
                <select x-model="newSource" class="form-input w-full">
                    <option value="">General</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="twitter">Twitter/X</option>
                    <option value="tiktok">TikTok</option>
                    <option value="email">Email</option>
                </select>
            </div>
            <button @click="createLink()" :disabled="creating || !newMerchantId"
                class="btn-primary px-4 py-2 text-sm whitespace-nowrap w-full sm:w-auto"
                :class="creating ? 'opacity-50' : ''">
                <span x-text="creating ? 'Creating...' : '🔗 Generate Link'"></span>
            </button>
        </div>
    </div>

    {{-- Existing Links --}}
    <template x-if="links.length > 0">
        <div>
            <h3 class="font-semibold text-sm mb-3">📋 My Links (<span x-text="links.length"></span>)</h3>
            <div class="space-y-4">
                <template x-for="link in links" :key="link.id">
                    <div class="card p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="font-semibold text-sm" x-text="link.merchant?.company_name || 'Business'"></div>
                                <div class="text-xs text-surface-500 mt-0.5">Source: <span x-text="link.source || 'General'"></span></div>
                            </div>
                            <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-medium" x-text="link.status"></span>
                        </div>

                        {{-- Referral URL --}}
                        <div class="flex items-center gap-2 bg-surface-50 rounded-lg p-2.5 mb-3">
                            <code class="text-xs text-surface-700 flex-1 break-all" x-text="'https://bonushub.my/ref/' + link.referral_code"></code>
                            <button @click="copyLink(link.referral_code)" class="btn-primary text-xs px-3 py-1.5 flex-shrink-0">📋 Copy</button>
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-4 gap-2">
                            <div class="ref-stat bg-green-50"><span class="num text-green-600" x-text="link.total_clicks || 0"></span><span class="lbl">Clicks</span></div>
                            <div class="ref-stat bg-blue-50"><span class="num text-blue-600" x-text="link.total_signups || 0"></span><span class="lbl">Signups</span></div>
                            <div class="ref-stat bg-purple-50"><span class="num text-purple-600" x-text="link.total_conversions || 0"></span><span class="lbl">Converted</span></div>
                            <div class="ref-stat bg-orange-50"><span class="num text-orange-600" x-text="link.points_earned || 0"></span><span class="lbl">Points</span></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Empty state --}}
    <template x-if="links.length === 0 && !loading">
        <div class="text-center py-16 text-surface-400">
            <div class="text-5xl mb-3">🔗</div>
            <h3 class="font-semibold text-surface-600 mb-1">No referral links yet</h3>
            <p class="text-sm">Create your first link above and start sharing!</p>
        </div>
    </template>

    {{-- Toast --}}
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 bg-green-600 text-white px-5 py-3 rounded-lg font-semibold shadow-lg z-50 text-sm" x-text="toast"></div>
</div>
@endsection

@push('scripts')
<script>
function referralManager() {
    return {
        links: [], loading: true, creating: false, newMerchantId: '', newSource: '', toast: '',
        async init() { await this.loadLinks(); },
        async loadLinks() {
            try {
                const resp = await fetch('/customer/api/referrals', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                if (data.success) this.links = data.referrals || [];
            } catch (e) { console.log('Load error:', e); } finally { this.loading = false; }
        },
        async createLink() {
            if (!this.newMerchantId) return;
            this.creating = true;
            try {
                const resp = await fetch('/customer/api/referrals/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ merchant_id: this.newMerchantId, source: this.newSource })
                });
                const data = await resp.json();
                if (data.success) { this.showToast(data.message || 'Link created!'); await this.loadLinks(); this.newMerchantId = ''; this.newSource = ''; }
                else { this.showToast(data.message || 'Error'); }
            } catch (e) { this.showToast('Error: ' + e.message); } finally { this.creating = false; }
        },
        copyLink(code) {
            const url = 'https://bonushub.my/ref/' + code;
            navigator.clipboard.writeText(url).then(() => this.showToast('📋 Copied!')).catch(() => {
                const ta = document.createElement('textarea'); ta.value = url; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); this.showToast('📋 Copied!');
            });
        },
        showToast(msg) { this.toast = msg; setTimeout(() => this.toast = '', 3000); }
    }
}
</script>
@endpush
