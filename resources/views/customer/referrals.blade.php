@extends('layouts.app')
@section('title', 'My Referral Links')
@section('content')
<div class="page-container" style="padding-top:0" x-data="referralManager()">
    <div class="page-header">
        <div>
            <h1 class="page-title">🔗 My Referral Links</h1>
            <p class="page-subtitle">Share with friends and earn points for each signup!</p>
        </div>
    </div>

    <!-- Create New Link -->
    <div style="background:white;border-radius:12px;padding:20px;margin-bottom:20px;border:2px dashed #6366f1">
        <h3 style="font-weight:600;margin-bottom:8px">✨ Create Referral Link</h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
            <div style="flex:1;min-width:200px">
                <label class="form-label" style="font-size:12px;color:#666">Business</label>
                <select x-model="newMerchantId" class="form-input" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;width:100%">
                    <option value="">Select business...</option>
                    @foreach(\App\Models\Merchant::where('status','approved')->get() as $m)
                    <option value="{{ $m->id }}">{{ $m->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:150px">
                <label class="form-label" style="font-size:12px;color:#666">Source (optional)</label>
                <select x-model="newSource" class="form-input" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;width:100%">
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
                style="background:#6366f1;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600;white-space:nowrap"
                :style="creating ? 'opacity:0.5' : ''">
                <span x-text="creating ? 'Creating...' : '🔗 Generate Link'"></span>
            </button>
        </div>
    </div>

    <!-- Existing Links -->
    <template x-if="links.length > 0">
        <div>
            <h3 style="font-weight:600;margin-bottom:12px">📋 My Links (<span x-text="links.length"></span>)</h3>
            <template x-for="link in links" :key="link.id">
                <div style="background:white;border-radius:12px;padding:16px;margin-bottom:12px;border:1px solid #e5e7eb">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px">
                        <div>
                            <div style="font-weight:600;font-size:15px" x-text="link.merchant?.company_name || 'Business'"></div>
                            <div style="font-size:12px;color:#6b7280;margin-top:2px">
                                Source: <span x-text="link.source || 'General'"></span>
                            </div>
                        </div>
                        <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600"
                            x-text="link.status"></span>
                    </div>

                    <!-- Referral URL -->
                    <div style="background:#f3f4f6;padding:10px 12px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <code style="font-size:12px;color:#374151;word-break:break-all;flex:1;margin-right:8px"
                            x-text="'https://bonushub.my/ref/' + link.referral_code"></code>
                        <button @click="copyLink(link.referral_code)"
                            style="background:#6366f1;color:white;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;font-size:12px;white-space:nowrap">
                            📋 Copy
                        </button>
                    </div>

                    <!-- Stats -->
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
                        <div style="text-align:center;padding:8px;background:#f0fdf4;border-radius:8px">
                            <div style="font-size:20px;font-weight:700;color:#16a34a" x-text="link.total_clicks || 0"></div>
                            <div style="font-size:11px;color:#6b7280">Clicks</div>
                        </div>
                        <div style="text-align:center;padding:8px;background:#eff6ff;border-radius:8px">
                            <div style="font-size:20px;font-weight:700;color:#2563eb" x-text="link.total_signups || 0"></div>
                            <div style="font-size:11px;color:#6b7280">Signups</div>
                        </div>
                        <div style="text-align:center;padding:8px;background:#faf5ff;border-radius:8px">
                            <div style="font-size:20px;font-weight:700;color:#9333ea" x-text="link.total_conversions || 0"></div>
                            <div style="font-size:11px;color:#6b7280">Converted</div>
                        </div>
                        <div style="text-align:center;padding:8px;background:#fff7ed;border-radius:8px">
                            <div style="font-size:20px;font-weight:700;color:#ea580c" x-text="link.points_earned || 0"></div>
                            <div style="font-size:11px;color:#6b7280">Points</div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Empty state -->
    <template x-if="links.length === 0 && !loading">
        <div style="text-align:center;padding:60px 20px;color:#6b7280">
            <div style="font-size:48px;margin-bottom:12px">🔗</div>
            <h3 style="font-weight:600;margin-bottom:4px">No referral links yet</h3>
            <p style="font-size:14px">Create your first link above and start sharing!</p>
        </div>
    </template>

    <!-- Toast -->
    <div x-show="toast" x-transition
        style="position:fixed;bottom:20px;right:20px;background:#16a34a;color:white;padding:12px 20px;border-radius:8px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999"
        x-text="toast"></div>
</div>
@endsection

@push('scripts')
<script>
function referralManager() {
    return {
        links: [],
        loading: true,
        creating: false,
        newMerchantId: '',
        newSource: '',
        toast: '',

        async init() {
            await this.loadLinks();
        },

        async loadLinks() {
            // Load from API or use server-rendered data
            try {
                const resp = await fetch('/customer/api/referrals', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await resp.json();
                if (data.success) this.links = data.referrals || [];
            } catch (e) {
                console.log('Load error:', e);
            } finally {
                this.loading = false;
            }
        },

        async createLink() {
            if (!this.newMerchantId) return;
            this.creating = true;
            try {
                const resp = await fetch('/customer/api/referrals/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ merchant_id: this.newMerchantId, source: this.newSource })
                });
                const data = await resp.json();
                if (data.success) {
                    this.showToast(data.message || 'Link created!');
                    await this.loadLinks();
                    this.newMerchantId = '';
                    this.newSource = '';
                } else {
                    this.showToast(data.message || 'Error creating link');
                }
            } catch (e) {
                this.showToast('Error: ' + e.message);
            } finally {
                this.creating = false;
            }
        },

        copyLink(code) {
            const url = 'https://bonushub.my/ref/' + code;
            navigator.clipboard.writeText(url).then(() => {
                this.showToast('📋 Link copied to clipboard!');
            }).catch(() => {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.showToast('📋 Link copied!');
            });
        },

        showToast(msg) {
            this.toast = msg;
            setTimeout(() => this.toast = '', 3000);
        }
    }
}
</script>
@endpush
