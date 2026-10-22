@extends('layouts.app')
@section('title', 'Dashboard - BonusHub')
@section('content')
<style>
/* Superadmin dashboard: scoped styles in the app palette (bonus = indigo, gold, surface = slate), light + dark. */
.sd-sec { margin-bottom:20px; }
.sd-h { font-size:15px; font-weight:700; color:#0f172a; margin:0; }
.dark .sd-h { color:#f1f5f9; }
.sd-sub { font-size:12px; color:#64748b; margin-top:2px; }

/* attention strip */
.sd-attn { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
@media (max-width:820px){ .sd-attn { grid-template-columns:1fr; } }
.sd-alert { display:flex; align-items:center; gap:14px; padding:14px 16px; text-decoration:none; color:inherit; border-left:4px solid #10b981; }
.sd-alert.warn { border-left-color:#f59e0b; background:linear-gradient(90deg,#fffbeb,transparent 70%); }
.sd-alert.bad { border-left-color:#ef4444; background:linear-gradient(90deg,#fef2f2,transparent 70%); }
.dark .sd-alert.warn { background:linear-gradient(90deg,rgba(245,158,11,.12),transparent 70%); }
.dark .sd-alert.bad { background:linear-gradient(90deg,rgba(239,68,68,.12),transparent 70%); }
.sd-alert b { font-size:26px; line-height:1; font-weight:800; color:#0f172a; }
.dark .sd-alert b { color:#fff; }
.sd-alert span.l { display:block; font-size:13px; font-weight:600; color:#1e293b; }
.dark .sd-alert span.l { color:#e2e8f0; }
.sd-alert span.s { display:block; font-size:11.5px; color:#64748b; margin-top:1px; }
.sd-alert .go { margin-left:auto; color:#94a3b8; font-size:18px; }

/* KPI cards */
.sd-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
@media (max-width:1100px){ .sd-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width:560px){ .sd-kpis { grid-template-columns:1fr; } }
.sd-kpi { padding:16px 18px; position:relative; overflow:hidden; }
.sd-kpi::after { content:''; position:absolute; right:-24px; top:-24px; width:90px; height:90px; border-radius:50%; background:var(--sd-tint,#eef2ff); opacity:.7; }
.dark .sd-kpi::after { opacity:.12; }
.sd-kpi-top { display:flex; align-items:center; gap:10px; position:relative; z-index:1; }
.sd-ico { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:18px; background:var(--sd-tint,#eef2ff); flex-shrink:0; }
.sd-ico svg { width:20px; height:20px; }
.sd-kpi-l { font-size:12.5px; font-weight:600; color:#64748b; }
.sd-kpi-v { font-size:30px; line-height:1.1; font-weight:800; color:#0f172a; margin-top:10px; letter-spacing:-.02em; position:relative; z-index:1; }
.dark .sd-kpi-v { color:#fff; }
.sd-kpi-s { font-size:12px; color:#64748b; margin-top:4px; position:relative; z-index:1; }
.sd-delta { display:inline-block; font-size:11px; font-weight:700; padding:2px 7px; border-radius:999px; margin-left:6px; vertical-align:middle; }
.sd-delta.up { background:#dcfce7; color:#15803d; }
.sd-delta.flat { background:#f1f5f9; color:#64748b; }
.dark .sd-delta.up { background:rgba(16,185,129,.2); color:#6ee7b7; }
.dark .sd-delta.flat { background:#334155; color:#94a3b8; }

/* two-column rows */
.sd-row { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:16px; }
@media (max-width:900px){ .sd-row { grid-template-columns:1fr; } }
.sd-card { padding:18px 20px; }
.sd-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:14px; }
.sd-link { font-size:12px; font-weight:600; color:#4f46e5; text-decoration:none; white-space:nowrap; }
.dark .sd-link { color:#a5b4fc; }

/* plan distribution */
.sd-plan { margin-bottom:12px; }
.sd-plan-top { display:flex; justify-content:space-between; align-items:baseline; font-size:13px; margin-bottom:5px; }
.sd-plan-top b { font-weight:600; color:#1e293b; }
.dark .sd-plan-top b { color:#e2e8f0; }
.sd-plan-top span { font-size:12px; color:#64748b; }
.sd-bar { height:9px; border-radius:999px; background:#f1f5f9; overflow:hidden; }
.dark .sd-bar { background:#334155; }
.sd-bar i { display:block; height:100%; border-radius:999px; background:linear-gradient(90deg,#6366f1,#a855f7); }
.sd-bar i.free { background:linear-gradient(90deg,#cbd5e1,#94a3b8); }
.sd-bar i.top { background:linear-gradient(90deg,#fbbf24,#f59e0b); }
.sd-mrr { display:flex; justify-content:space-between; align-items:center; margin-top:14px; padding:10px 12px; border-radius:10px; background:#f8fafc; font-size:12.5px; color:#475569; }
.dark .sd-mrr { background:rgba(15,23,42,.5); color:#cbd5e1; }
.sd-mrr b { font-size:16px; color:#0f172a; }
.dark .sd-mrr b { color:#fff; }

/* bar chart */
.sd-chart { display:flex; align-items:flex-end; gap:6px; height:130px; padding-top:8px; }
.sd-col { flex:1; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; height:100%; min-width:0; }
.sd-col i { display:block; width:100%; max-width:26px; border-radius:6px 6px 2px 2px; background:linear-gradient(180deg,#818cf8,#6366f1); min-height:3px; }
.sd-col i.zero { background:#e2e8f0; }
.dark .sd-col i.zero { background:#334155; }
.sd-col span { font-size:9.5px; color:#94a3b8; margin-top:5px; }
.sd-chart-foot { display:flex; justify-content:space-between; margin-top:12px; font-size:12px; color:#64748b; }
.sd-chart-foot b { color:#0f172a; }
.dark .sd-chart-foot b { color:#fff; }

/* usage tiles */
.sd-tiles { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
@media (max-width:560px){ .sd-tiles { grid-template-columns:repeat(2,minmax(0,1fr)); } }
.sd-tile { background:#f8fafc; border:1px solid #eef2f6; border-radius:10px; padding:10px 12px; }
.dark .sd-tile { background:rgba(15,23,42,.5); border-color:#334155; }
.sd-tile b { display:block; font-size:20px; font-weight:700; color:#0f172a; }
.dark .sd-tile b { color:#f1f5f9; }
.sd-tile span { font-size:11.5px; color:#64748b; }

/* merchant list */
.sd-m { display:flex; align-items:center; gap:12px; padding:10px 0; border-top:1px solid #f1f5f9; }
.sd-m:first-of-type { border-top:0; padding-top:0; }
.dark .sd-m { border-color:#334155; }
.sd-av { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; font-weight:700; font-size:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sd-m-n { font-size:13.5px; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dark .sd-m-n { color:#e2e8f0; }
.sd-m-s { font-size:11.5px; color:#64748b; }
.sd-empty { font-size:13px; color:#94a3b8; text-align:center; padding:18px 0; }

/* health + quick links */
.sd-health { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
@media (max-width:900px){ .sd-health { grid-template-columns:repeat(2,minmax(0,1fr)); } }
.sd-hi { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; border:1px solid #e2e8f0; font-size:12.5px; color:#475569; }
.dark .sd-hi { border-color:#334155; color:#cbd5e1; }
.sd-dot { width:9px; height:9px; border-radius:50%; background:#10b981; flex-shrink:0; box-shadow:0 0 0 3px rgba(16,185,129,.18); }
.sd-dot.warn { background:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
.sd-dot.bad { background:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.2); }
.sd-hi b { display:block; font-size:13px; color:#0f172a; }
.dark .sd-hi b { color:#fff; }
.sd-quick { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; }
@media (max-width:1000px){ .sd-quick { grid-template-columns:repeat(2,minmax(0,1fr)); } }
.sd-q { display:flex; align-items:center; gap:12px; padding:14px 16px; text-decoration:none; color:inherit; }
.sd-q .sd-ico { color:#fff; }
.sd-q b { display:block; font-size:13.5px; color:#1e293b; }
.dark .sd-q b { color:#e2e8f0; }
.sd-q span.d { font-size:11.5px; color:#64748b; }
</style>

<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Overview of the BonusHub platform</p>
        </div>
        <div class="text-sm text-surface-400" id="last-updated">Updated {{ $d['generated_at'] }}</div>
    </div>

    {{-- ===== Needs attention ===== --}}
    <div class="sd-sec sd-attn">
        <a href="{{ route('superadmin.merchants.pending') }}" class="card sd-alert {{ $d['attention']['pending'] > 0 ? 'warn' : '' }}" data-k="attn-pending">
            <b>{{ $d['attention']['pending'] }}</b>
            <div><span class="l">Merchants awaiting approval</span><span class="s">{{ $d['attention']['pending'] > 0 ? 'Review their IC / SSM documents' : 'Nothing waiting' }}</span></div>
            <span class="go">›</span>
        </a>
        <a href="{{ route('superadmin.merchants') }}" class="card sd-alert {{ $d['attention']['expiring'] > 0 ? 'warn' : '' }}" data-k="attn-expiring">
            <b>{{ $d['attention']['expiring'] }}</b>
            <div><span class="l">Subscriptions expiring in 7 days</span><span class="s">{{ $d['attention']['expiring'] > 0 ? 'They turn read-only 7 days after expiry' : 'No upcoming expiry' }}</span></div>
            <span class="go">›</span>
        </a>
        <a href="{{ route('superadmin.merchants') }}" class="card sd-alert {{ $d['attention']['lapsed'] > 0 ? 'bad' : '' }}" data-k="attn-lapsed">
            <b>{{ $d['attention']['lapsed'] }}</b>
            <div><span class="l">Lapsed (read-only) merchants</span><span class="s">{{ $d['attention']['lapsed'] > 0 ? 'Subscription expired more than 7 days ago' : 'No lapsed accounts' }}</span></div>
            <span class="go">›</span>
        </a>
    </div>

    {{-- ===== KPIs ===== --}}
    <div class="sd-sec sd-kpis">
        <div class="card sd-kpi" style="--sd-tint:#eef2ff">
            <div class="sd-kpi-top"><div class="sd-ico">🏪</div><div class="sd-kpi-l">Merchants</div></div>
            <div class="sd-kpi-v" data-k="merchants">{{ number_format($d['merchants']['total']) }}</div>
            <div class="sd-kpi-s">{{ $d['merchants']['active'] }} active · {{ $d['merchants']['pending'] }} pending · {{ $d['merchants']['rejected'] }} rejected
                <span class="sd-delta {{ $d['merchants']['new_month'] > 0 ? 'up' : 'flat' }}">{{ $d['merchants']['new_month'] > 0 ? '+' : '' }}{{ $d['merchants']['new_month'] }} this month</span></div>
        </div>
        <div class="card sd-kpi" style="--sd-tint:#dbeafe">
            <div class="sd-kpi-top"><div class="sd-ico">👥</div><div class="sd-kpi-l">Customers</div></div>
            <div class="sd-kpi-v" data-k="customers">{{ number_format($d['customers']['total']) }}</div>
            <div class="sd-kpi-s">{{ number_format($d['customers']['with_merchant']) }} joined a merchant
                <span class="sd-delta {{ $d['customers']['new_month'] > 0 ? 'up' : 'flat' }}">{{ $d['customers']['new_month'] > 0 ? '+' : '' }}{{ $d['customers']['new_month'] }} this month</span></div>
        </div>
        <div class="card sd-kpi" style="--sd-tint:#f3e8ff">
            <div class="sd-kpi-top"><div class="sd-ico">🧑‍💼</div><div class="sd-kpi-l">Staff accounts</div></div>
            <div class="sd-kpi-v" data-k="staff">{{ number_format($d['staff']) }}</div>
            <div class="sd-kpi-s">across all merchants</div>
        </div>
        <div class="card sd-kpi" style="--sd-tint:#fef3c7">
            <div class="sd-kpi-top"><div class="sd-ico">💎</div><div class="sd-kpi-l">Est. monthly revenue</div></div>
            <div class="sd-kpi-v" data-k="mrr">RM{{ number_format($d['mrr'], 0) }}</div>
            <div class="sd-kpi-s">from plan prices × merchants on each plan</div>
        </div>
    </div>

    {{-- ===== Plans + points chart ===== --}}
    <div class="sd-sec sd-row">
        <div class="card sd-card">
            <div class="sd-card-head">
                <div><h2 class="sd-h">Merchants by plan</h2><div class="sd-sub">{{ $d['merchants']['total'] }} merchants across {{ count($d['plans']) }} plans</div></div>
                <a href="{{ route('superadmin.packages') }}" class="sd-link">Manage plans ›</a>
            </div>
            @forelse($d['plans'] as $pl)
                <div class="sd-plan" data-plan="{{ $pl['id'] }}">
                    <div class="sd-plan-top"><b>{{ $pl['name'] }}</b><span>{{ $pl['merchants'] }} {{ \Illuminate\Support\Str::plural('merchant', $pl['merchants']) }} · {{ $pl['share'] }}% · {{ $pl['price'] == 0 ? 'Free' : 'RM' . number_format($pl['price'], 0) . '/mo' }}</span></div>
                    <div class="sd-bar"><i class="{{ $pl['tier'] }}" style="width:{{ max($pl['share'], $pl['merchants'] > 0 ? 3 : 0) }}%"></i></div>
                </div>
            @empty
                <div class="sd-empty">No packages yet.</div>
            @endforelse
            @if($d['no_package'] > 0)
                <div class="sd-sub">{{ $d['no_package'] }} merchant(s) have no package and follow the Free plan.</div>
            @endif
        </div>

        <div class="card sd-card">
            <div class="sd-card-head">
                <div><h2 class="sd-h">Points issued</h2><div class="sd-sub">Approved earnings, last 14 days (all merchants)</div></div>
            </div>
            <div class="sd-chart" data-k="chart">
                @foreach($d['chart'] as $c)
                    <div class="sd-col" title="{{ $c['label'] }}: {{ number_format($c['value']) }} pts"><i class="{{ $c['value'] > 0 ? '' : 'zero' }}" style="height:{{ $c['value'] > 0 ? max(6, $c['pct']) : 3 }}%"></i><span>{{ $c['day'] }}</span></div>
                @endforeach
            </div>
            <div class="sd-chart-foot"><span>Last 14 days: <b data-k="points14">{{ number_format($d['points_14']) }}</b> pts</span><span>Last 30 days: <b data-k="points30">{{ number_format($d['points_30']) }}</b> pts</span></div>
        </div>
    </div>

    {{-- ===== Usage + latest merchants ===== --}}
    <div class="sd-sec sd-row">
        <div class="card sd-card">
            <div class="sd-card-head"><div><h2 class="sd-h">Platform usage</h2><div class="sd-sub">Last 30 days unless stated</div></div></div>
            <div class="sd-tiles">
                <div class="sd-tile"><b data-k="redemptions">{{ number_format($d['usage']['redemptions']) }}</b><span>Redemptions</span></div>
                <div class="sd-tile"><b data-k="referrals">{{ number_format($d['usage']['referrals']) }}</b><span>Valid referrals</span></div>
                <div class="sd-tile"><b data-k="new-customers">{{ number_format($d['usage']['new_customers']) }}</b><span>New customers</span></div>
                <div class="sd-tile"><b data-k="giveaways">{{ number_format($d['usage']['giveaways']) }}</b><span>Active giveaways (now)</span></div>
                <div class="sd-tile"><b data-k="tasks">{{ number_format($d['usage']['tasks']) }}</b><span>Active viral tasks (now)</span></div>
                <div class="sd-tile"><b data-k="links">{{ number_format($d['usage']['campaign_clicks']) }}</b><span>Referral Campaign clicks</span></div>
            </div>
        </div>

        <div class="card sd-card">
            <div class="sd-card-head">
                <div><h2 class="sd-h">Latest merchants</h2><div class="sd-sub">Newest sign-ups</div></div>
                <a href="{{ route('superadmin.merchants') }}" class="sd-link">All merchants ›</a>
            </div>
            @forelse($d['latest'] as $m)
                <div class="sd-m" data-merchant="{{ $m['id'] }}">
                    <div class="sd-av">{{ strtoupper(mb_substr($m['name'], 0, 1)) }}</div>
                    <div style="min-width:0;flex:1">
                        <div class="sd-m-n">{{ $m['name'] }}</div>
                        <div class="sd-m-s">{{ $m['package'] ?? 'No package' }} · joined {{ $m['joined'] }}</div>
                    </div>
                    <span class="{{ $m['status'] === 'active' ? 'badge-success' : ($m['status'] === 'pending' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($m['status']) }}</span>
                </div>
            @empty
                <div class="sd-empty">No merchants yet.</div>
            @endforelse
        </div>
    </div>

    {{-- ===== System health ===== --}}
    <div class="sd-sec">
        <div class="sd-card-head" style="margin-bottom:10px"><div><h2 class="sd-h">System</h2></div></div>
        <div class="sd-health">
            <div class="sd-hi"><span class="sd-dot {{ $d['system']['failed_jobs'] > 0 ? 'bad' : '' }}"></span><div><b>{{ $d['system']['failed_jobs'] }} failed jobs</b>{{ $d['system']['waiting_jobs'] }} waiting in the queue</div></div>
            <div class="sd-hi"><span class="sd-dot {{ $d['system']['expiry'] ? '' : 'warn' }}"></span><div><b>Points expiry {{ $d['system']['expiry'] ? 'ON' : 'OFF' }}</b>{{ $d['system']['expiry'] ? 'runs daily at 03:45' : 'nothing expires' }}</div></div>
            <div class="sd-hi"><span class="sd-dot"></span><div><b>Mail: {{ $d['system']['mailer'] }}</b>queued, sent every minute</div></div>
            <div class="sd-hi"><span class="sd-dot"></span><div><b>Laravel {{ $d['system']['laravel'] }}</b>PHP {{ $d['system']['php'] }}</div></div>
        </div>
    </div>

    {{-- ===== Quick links ===== --}}
    <div class="sd-quick">
        <a href="{{ route('superadmin.merchants') }}" class="card card-hover sd-q"><div class="sd-ico" style="background:linear-gradient(135deg,#6366f1,#a855f7)">🏪</div><div><b>Merchants</b><span class="d">View, approve, manage</span></div></a>
        <a href="{{ route('superadmin.packages') }}" class="card card-hover sd-q"><div class="sd-ico" style="background:linear-gradient(135deg,#fbbf24,#f97316)">📦</div><div><b>Packages</b><span class="d">Plans, limits, tools</span></div></a>
        <a href="{{ route('superadmin.leaderboard') }}" class="card card-hover sd-q"><div class="sd-ico" style="background:linear-gradient(135deg,#10b981,#0d9488)">🏆</div><div><b>Leaderboard</b><span class="d">Top customers</span></div></a>
        <a href="{{ route('superadmin.audit') }}" class="card card-hover sd-q"><div class="sd-ico" style="background:linear-gradient(135deg,#64748b,#334155)">🧾</div><div><b>Audit trail</b><span class="d">Who did what</span></div></a>
        <a href="{{ route('settings') }}" class="card card-hover sd-q"><div class="sd-ico" style="background:linear-gradient(135deg,#0ea5e9,#2563eb)">⚙️</div><div><b>Settings</b><span class="d">Payments &amp; platform</span></div></a>
    </div>
</div>
@endsection
