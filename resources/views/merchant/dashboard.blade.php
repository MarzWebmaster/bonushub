@extends('layouts.app')
@section('title', 'Dashboard — Merchant')
@section('content')
<div class="page-container" style="padding-top:0" x-data="merchantDashboard()">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, <span x-text="data.merchant_name">...</span> 👋</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
        <template x-for="(stat, i) in data.stats" :key="i">
            <div style="background:white;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.08)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <span style="font-size:28px" x-text="stat.icon"></span>
                    <span x-show="stat.trend"
                          :style="'font-size:12px;font-weight:600;color:' + (stat.trend > 0 ? '#10b981' : '#ef4444')"
                          x-text="(stat.trend > 0 ? '↑' : '↓') + Math.abs(stat.trend) + '%'"></span>
                </div>
                <div style="font-size:28px;font-weight:700;color:#111827" x-text="stat.value"></div>
                <div style="font-size:13px;color:#6b7280;margin-top:4px" x-text="stat.label"></div>
            </div>
        </template>
    </div>

    <!-- Two columns: Recent Activity + Quick Actions -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
        <!-- Recent Activity -->
        <div style="background:white;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.08)">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Recent Activity</h3>
            <template x-if="data.recent_activity.length === 0">
                <p style="color:#9ca3af;font-size:14px">No recent activity yet</p>
            </template>
            <div style="display:flex;flex-direction:column;gap:12px">
                <template x-for="(activity, i) in data.recent_activity" :key="i">
                    <div style="display:flex;align-items:center;gap:12px;padding:10px;background:#f9fafb;border-radius:8px">
                        <span style="font-size:20px" x-text="activity.icon"></span>
                        <div style="flex:1">
                            <div style="font-size:14px;font-weight:500" x-text="activity.title"></div>
                            <div style="font-size:12px;color:#6b7280" x-text="activity.subtitle"></div>
                        </div>
                        <div style="font-size:12px;color:#9ca3af;white-space:nowrap" x-text="activity.time"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="background:white;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.08)">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Quick Actions</h3>
            <div style="display:flex;flex-direction:column;gap:10px">
                <a href="{{ route('merchant.dashboard') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;text-decoration:none;color:#111">
                    <span style="font-size:20px">📊</span>
                    <div>
                        <div style="font-size:14px;font-weight:600">Dashboard</div>
                        <div style="font-size:12px;color:#6b7280">View your business overview</div>
                    </div>
                </a>
                <a href="{{ route('merchant.rewards.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fefce8;border:1px solid #fde68a;border-radius:8px;text-decoration:none;color:#111">
                    <span style="font-size:20px">🎁</span>
                    <div>
                        <div style="font-size:14px;font-weight:600">Add Reward</div>
                        <div style="font-size:12px;color:#6b7280">Create new reward for customers</div>
                    </div>
                </a>
                <a href="{{ route('merchant.tasks') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;text-decoration:none;color:#111">
                    <span style="font-size:20px">📋</span>
                    <div>
                        <div style="font-size:14px;font-weight:600">Create Task</div>
                        <div style="font-size:12px;color:#6b7280">Viral task for customers</div>
                    </div>
                </a>
                <a href="{{ route('merchant.giveaways') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;text-decoration:none;color:#111">
                    <span style="font-size:20px">🎉</span>
                    <div>
                        <div style="font-size:14px;font-weight:600">Start Giveaway</div>
                        <div style="font-size:12px;color:#6b7280">Run a referral campaign</div>
                    </div>
                </a>
                <a href="{{ route('merchant.analytics') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;text-decoration:none;color:#111">
                    <span style="font-size:20px">📊</span>
                    <div>
                        <div style="font-size:14px;font-weight:600">Analytics</div>
                        <div style="font-size:12px;color:#6b7280">View reports & insights</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function merchantDashboard() {
    return {
        data: {
            merchant_name: 'Merchant',
            stats: [],
            recent_activity: []
        },
        async init() {
            try {
                const res = await fetch('/merchant/api/dashboard-overview', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) this.data = await res.json();
            } catch(e) { console.error('Dashboard load failed:', e); }
        }
    };
}
</script>
@endsection