@extends('layouts.app')
@section('title', 'Dashboard — Merchant')
@section('content')
<style>
/* Dashboard Responsive Styles */
.dash-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
@media (min-width: 640px) {
    .dash-stats-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
}
.stat-card {
    background: white;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    position: relative;
    overflow: hidden;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #a855f7);
    opacity: 0;
    transition: opacity 0.2s;
}
.stat-card:active { transform: scale(0.97); }
@media (hover: hover) {
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .stat-card:hover::before { opacity: 1; }
}
.stat-icon { font-size: 24px; }
@media (min-width: 640px) { .stat-icon { font-size: 28px; } }
.stat-value { font-size: 22px; font-weight: 700; color: #111827; line-height: 1.2; }
@media (min-width: 640px) { .stat-value { font-size: 28px; } }
.stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }
@media (min-width: 640px) { .stat-label { font-size: 13px; } }

.dash-content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 768px) {
    .dash-content-grid { grid-template-columns: 2fr 1fr; }
}
.dash-card {
    background: white;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}
@media (min-width: 640px) { .dash-card { padding: 20px; } }
.dash-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 14px; color: #111827; }
@media (min-width: 640px) { .dash-card h3 { font-size: 16px; margin-bottom: 16px; } }

.activity-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f9fafb;
    border-radius: 10px;
    transition: background 0.15s;
}
@media (min-width: 640px) { .activity-item { gap: 12px; } }
.activity-item:active { background: #f3f4f6; }

.quick-action {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    color: #111;
    transition: transform 0.12s ease, box-shadow 0.12s ease;
    -webkit-tap-highlight-color: transparent;
}
.quick-action:active { transform: scale(0.97); }
@media (hover: hover) {
    .quick-action:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
}
.quick-action-icon { font-size: 22px; flex-shrink: 0; }
@media (min-width: 640px) { .quick-action-icon { font-size: 20px; } }
.quick-action-title { font-size: 13px; font-weight: 600; }
@media (min-width: 640px) { .quick-action-title { font-size: 14px; } }
.quick-action-desc { font-size: 11px; color: #6b7280; margin-top: 2px; }
@media (min-width: 640px) { .quick-action-desc { font-size: 12px; } }

/* Quick actions grid on mobile for better touch targets */
.quick-actions-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}
@media (min-width: 400px) {
    .quick-actions-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
}
@media (min-width: 768px) {
    .quick-actions-grid { grid-template-columns: 1fr; }
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 24px 16px;
    color: #9ca3af;
}
.empty-state-icon { font-size: 32px; margin-bottom: 8px; opacity: 0.5; }
.empty-state-text { font-size: 13px; }
@media (min-width: 640px) { .empty-state-text { font-size: 14px; } }
</style>

<div class="page-container" style="padding-top:0" x-data="merchantDashboard()">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, <span x-text="data.merchant_name">...</span> 👋</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="dash-stats-grid">
        <template x-for="(stat, i) in data.stats" :key="i">
            <div class="stat-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <span class="stat-icon" x-text="stat.icon"></span>
                    <span x-show="stat.trend"
                          :style="'font-size:11px;font-weight:600;padding:2px 6px;border-radius:6px;background:' + (stat.trend > 0 ? '#ecfdf5' : '#fef2f2') + ';color:' + (stat.trend > 0 ? '#10b981' : '#ef4444')"
                          x-text="(stat.trend > 0 ? '↑' : '↓') + Math.abs(stat.trend) + '%'"></span>
                </div>
                <div class="stat-value" x-text="stat.value"></div>
                <div class="stat-label" x-text="stat.label"></div>
            </div>
        </template>
    </div>

    <!-- Two columns: Recent Activity + Quick Actions -->
    <div class="dash-content-grid">
        <!-- Recent Activity -->
        <div class="dash-card">
            <h3>Recent Activity</h3>
            <template x-if="data.recent_activity.length === 0">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p class="empty-state-text">No recent activity yet</p>
                </div>
            </template>
            <div style="display:flex;flex-direction:column;gap:10px">
                <template x-for="(activity, i) in data.recent_activity" :key="i">
                    <div class="activity-item">
                        <span style="font-size:20px" x-text="activity.icon"></span>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" x-text="activity.title"></div>
                            <div style="font-size:11px;color:#6b7280" x-text="activity.subtitle"></div>
                        </div>
                        <div style="font-size:11px;color:#9ca3af;white-space:nowrap;flex-shrink:0" x-text="activity.time"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dash-card">
            <h3>Quick Actions</h3>
            <div class="quick-actions-grid">
                <a href="{{ route('merchant.dashboard') }}" class="quick-action" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <span class="quick-action-icon">📊</span>
                    <div>
                        <div class="quick-action-title">Dashboard</div>
                        <div class="quick-action-desc">View your business overview</div>
                    </div>
                </a>
                <a href="{{ route('merchant.rewards.index') }}" class="quick-action" style="background:#fefce8;border:1px solid #fde68a">
                    <span class="quick-action-icon">🎁</span>
                    <div>
                        <div class="quick-action-title">Add Reward</div>
                        <div class="quick-action-desc">Create new reward for customers</div>
                    </div>
                </a>
                <a href="{{ route('merchant.tasks') }}" class="quick-action" style="background:#eff6ff;border:1px solid #bfdbfe">
                    <span class="quick-action-icon">📋</span>
                    <div>
                        <div class="quick-action-title">Create Task</div>
                        <div class="quick-action-desc">Viral task for customers</div>
                    </div>
                </a>
                <a href="{{ route('merchant.giveaways.index') }}" class="quick-action" style="background:#fdf4ff;border:1px solid #e9d5ff">
                    <span class="quick-action-icon">🎉</span>
                    <div>
                        <div class="quick-action-title">Start Giveaway</div>
                        <div class="quick-action-desc">Run a referral campaign</div>
                    </div>
                </a>
                <a href="{{ route('merchant.analytics') }}" class="quick-action" style="background:#fff7ed;border:1px solid #fed7aa">
                    <span class="quick-action-icon">📈</span>
                    <div>
                        <div class="quick-action-title">Analytics</div>
                        <div class="quick-action-desc">View reports & insights</div>
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
