@extends('layouts.app')
@section('title', 'Campaign Links - Merchant')
@section('content')
<div class="page-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">📢 Campaign Links</h1>
            <p class="page-subtitle">Track where your customers come from</p>
        </div>
        <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="btn-primary">
            + New Campaign
        </button>
    </div>

    {{-- Analytics Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" id="stats-cards">
        <div class="stat-card border-l-bonus-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Total Visits</p>
            <p class="text-2xl font-bold text-surface-800 dark:text-white mt-1" id="stat-visits">—</p>
        </div>
        <div class="stat-card border-l-emerald-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Registrations</p>
            <p class="text-2xl font-bold text-surface-800 dark:text-white mt-1" id="stat-registrations">—</p>
        </div>
        <div class="stat-card border-l-purple-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Conversion Rate</p>
            <p class="text-2xl font-bold text-surface-800 dark:text-white mt-1" id="stat-conversion">—</p>
        </div>
        <div class="stat-card border-l-amber-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Active Campaigns</p>
            <p class="text-2xl font-bold text-surface-800 dark:text-white mt-1" id="stat-active">—</p>
        </div>
    </div>

    {{-- Registration Graph Section --}}
    <div class="card p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-surface-800 dark:text-white text-lg">📈 Registration Trend</h2>
            <select id="campaign-selector" class="form-select text-sm w-64" onchange="loadRegistrationChart()">
                <option value="">All Campaigns</option>
            </select>
        </div>
        <div style="position:relative;height:280px">
            <canvas id="registration-chart"></canvas>
        </div>
        <p class="text-xs text-surface-400 mt-2" id="chart-caption">Last 30 days</p>
    </div>

    {{-- Campaign List (single column) --}}
    <div id="campaigns-list">
        <div class="text-center text-surface-400 py-8">Loading campaigns...</div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">{{ $campaigns->links() }}</div>
</div>

{{-- ===== CREATE CAMPAIGN MODAL ===== --}}
<div id="create-modal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal-content max-w-lg">
        <div class="modal-header">
            <h2 class="text-lg font-bold">✨ New Campaign Link</h2>
            <button onclick="this.closest('.modal-overlay').classList.add('hidden')">&times;</button>
        </div>
        <form id="create-form" class="modal-body">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label">Campaign Name *</label>
                    <input name="name" class="form-input" placeholder="e.g. IG Promo June, Jalan TAR Flyer" required>
                </div>
                <div>
                    <label class="form-label">Source / Medium</label>
                    <select name="medium" class="form-select">
                        <option value="">— Select —</option>
                        <option value="instagram">📸 Instagram</option>
                        <option value="facebook">📘 Facebook</option>
                        <option value="whatsapp">💬 WhatsApp</option>
                        <option value="tiktok">🎵 TikTok</option>
                        <option value="twitter">🐦 Twitter/X</option>
                        <option value="flyer">📄 Flyer / QR Poster</option>
                        <option value="email">📧 Email</option>
                        <option value="other">🔗 Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Expiry Date (optional)</label>
                    <input name="expires_at" type="datetime-local" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="this.closest('.modal-overlay').classList.add('hidden')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Create Link</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== DETAIL MODAL (QR + Copy) ===== --}}
<div id="detail-modal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal-content max-w-md">
        <div class="modal-header">
            <h2 class="text-lg font-bold" id="detail-name">Campaign</h2>
            <button onclick="this.closest('.modal-overlay').classList.add('hidden')">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div id="detail-qr" class="mb-4"></div>
            <p class="text-sm text-surface-500 mb-2">Short Link</p>
            <div class="flex items-center gap-2 mb-4">
                <input id="detail-url" class="form-input text-center font-mono text-sm" readonly>
                <button onclick="copyLink()" class="btn-primary text-sm px-3 py-2 whitespace-nowrap">📋 Copy</button>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-xl font-bold text-bonus-600" id="detail-visits">0</p>
                    <p class="text-xs text-surface-400">Visits</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-emerald-600" id="detail-regs">0</p>
                    <p class="text-xs text-surface-400">Registrations</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-purple-600" id="detail-conv">0%</p>
                    <p class="text-xs text-surface-400">Conversion</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/") }}';
let regChart = null;

// ── Load analytics ──
fetch('/merchant/api/campaigns/analytics')
    .then(r => r.json())
    .then(d => {
        if (d.analytics) {
            document.getElementById('stat-visits').textContent = d.analytics.total_visits.toLocaleString();
            document.getElementById('stat-registrations').textContent = d.analytics.total_registrations.toLocaleString();
            document.getElementById('stat-conversion').textContent = d.analytics.avg_conversion + '%';
            document.getElementById('stat-active').textContent = d.analytics.active_campaigns;
        }
    });

// ── Load campaigns list + populate dropdown ──
const campaigns = @json($campaigns->items());
renderList(campaigns);
populateDropdown(campaigns);
loadRegistrationChart();

function populateDropdown(list) {
    const sel = document.getElementById('campaign-selector');
    list.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = `${c.name} (${c.registrations} regs)`;
        sel.appendChild(opt);
    });
}

function renderList(list) {
    const container = document.getElementById('campaigns-list');
    if (!list.length) {
        container.innerHTML = '<div class="text-center py-12"><p class="text-surface-400 text-lg">No campaigns yet</p><p class="text-surface-300 text-sm mt-1">Create your first campaign link to start tracking!</p></div>';
        return;
    }
    const mediumIcons = { instagram:'📸', facebook:'📘', whatsapp:'💬', tiktok:'🎵', twitter:'🐦', flyer:'📄', email:'📧', other:'🔗' };
    container.innerHTML = list.map(c => `
        <div class="card p-5 mb-4 hover:shadow-lg transition-shadow">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                {{-- Left: info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="font-bold text-surface-800 dark:text-white text-lg">${c.name}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${c.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface-100 text-surface-500'}">
                            ${c.status}
                        </span>
                    </div>
                    <p class="text-sm text-surface-400">${mediumIcons[c.medium] || '🔗'} ${c.medium || 'No source'} · Created ${c.created_at ? new Date(c.created_at).toLocaleDateString('en-MY', {day:'numeric',month:'short',year:'numeric'}) : '—'}</p>
                </div>
                {{-- Center: stats --}}
                <div class="flex gap-6 text-center shrink-0">
                    <div>
                        <p class="text-xl font-bold text-bonus-600">${c.visits}</p>
                        <p class="text-[10px] text-surface-400 uppercase">Visits</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-emerald-600">${c.registrations}</p>
                        <p class="text-[10px] text-surface-400 uppercase">Registered</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-purple-600">${c.visits > 0 ? ((c.registrations / c.visits) * 100).toFixed(1) : 0}%</p>
                        <p class="text-[10px] text-surface-400 uppercase">Conv</p>
                    </div>
                </div>
                {{-- Right: actions --}}
                <div class="flex gap-2 shrink-0">
                    <button onclick="showDetail('${c.slug}', '${c.name}', ${c.visits}, ${c.registrations})" class="btn-sm btn-primary">🔗 Link & QR</button>
                    <button onclick="toggleStatus(${c.id})" class="btn-sm ${c.status === 'active' ? 'btn-warning' : 'btn-success'}" title="Toggle status">
                        ${c.status === 'active' ? '⏸' : '▶️'}
                    </button>
                    <button onclick="deleteCampaign(${c.id})" class="btn-sm btn-danger" title="Delete">🗑</button>
                </div>
            </div>
        </div>
    `).join('');
}

// ── Registration Chart ──
function loadRegistrationChart() {
    const campaignId = document.getElementById('campaign-selector').value;
    let url = '/merchant/api/campaigns/registrations?days=30';
    if (campaignId) url += '&campaign_id=' + campaignId;

    fetch(url)
        .then(r => r.json())
        .then(d => {
            const canvas = document.getElementById('registration-chart');
            const caption = document.getElementById('chart-caption');

            if (regChart) regChart.destroy();

            // Build datasets
            const datasets = [];
            const colors = [
                { bg: 'rgba(16,185,129,0.15)', border: '#10b981' },
                { bg: 'rgba(99,102,241,0.15)', border: '#6366f1' },
                { bg: 'rgba(245,158,11,0.15)', border: '#f59e0b' },
                { bg: 'rgba(239,68,68,0.15)', border: '#ef4444' },
                { bg: 'rgba(168,85,247,0.15)', border: '#a855f7' },
            ];

            // Collect all unique days
            const allDays = new Set();
            d.campaigns.forEach(c => c.data.forEach(r => allDays.add(r.day)));
            const labels = [...allDays].sort();

            // If no data, show empty state
            if (!labels.length) {
                const today = new Date();
                for (let i = 29; i >= 0; i--) {
                    const dt = new Date(today);
                    dt.setDate(dt.getDate() - i);
                    labels.push(dt.toISOString().slice(0, 10));
                }
            }

            d.campaigns.forEach((c, i) => {
                const color = colors[i % colors.length];
                const dayMap = {};
                c.data.forEach(r => dayMap[r.day] = r.count);
                datasets.push({
                    label: c.campaign_name,
                    data: labels.map(d => dayMap[d] || 0),
                    backgroundColor: color.bg,
                    borderColor: color.border,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                });
            });

            // If no campaigns data, show empty line
            if (!datasets.length) {
                datasets.push({
                    label: 'No registrations yet',
                    data: labels.map(() => 0),
                    backgroundColor: 'rgba(148,163,184,0.1)',
                    borderColor: '#94a3b8',
                    borderWidth: 1,
                    fill: true,
                    tension: 0.3,
                });
            }

            regChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels.map(d => {
                        const dt = new Date(d + 'T00:00:00');
                        return dt.toLocaleDateString('en-MY', { day:'numeric', month:'short' });
                    }),
                    datasets: datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: {
                            display: datasets.length > 1,
                            position: 'top',
                            labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 12 } }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.9)',
                            titleFont: { size: 13 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 11 }, color: '#94a3b8' },
                            grid: { color: 'rgba(148,163,184,0.1)' }
                        },
                        x: {
                            ticks: { font: { size: 10 }, color: '#94a3b8', maxRotation: 45 },
                            grid: { display: false }
                        }
                    }
                }
            });

            caption.textContent = d.campaigns.length > 0
                ? `Last ${d.days} days · ${d.campaigns.length} campaign${d.campaigns.length > 1 ? 's' : ''} · ${d.campaigns.reduce((s, c) => s + c.data.reduce((a, r) => a + r.count, 0), 0)} total registrations`
                : `Last ${d.days} days · No registrations recorded yet`;
        });
}

// ── Create campaign ──
document.getElementById('create-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    fetch('/merchant/api/campaigns', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert(d.message || 'Error creating campaign');
        }
    });
});

// ── Show detail modal with QR ──
function showDetail(slug, name, visits, regs) {
    const url = BASE + '/r/' + slug;
    document.getElementById('detail-name').textContent = name;
    document.getElementById('detail-url').value = url;
    document.getElementById('detail-visits').textContent = visits;
    document.getElementById('detail-regs').textContent = regs;
    document.getElementById('detail-conv').textContent = visits > 0 ? ((regs / visits) * 100).toFixed(1) + '%' : '0%';

    const qrDiv = document.getElementById('detail-qr');
    qrDiv.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(url)}" alt="QR Code" class="mx-auto rounded-lg shadow-md">`;

    document.getElementById('detail-modal').classList.remove('hidden');
}

function copyLink() {
    const input = document.getElementById('detail-url');
    input.select();
    navigator.clipboard.writeText(input.value);
    const btn = event.target;
    btn.textContent = '✅ Copied!';
    setTimeout(() => btn.textContent = '📋 Copy', 1500);
}

// ── Toggle status ──
function toggleStatus(id) {
    fetch(`/merchant/api/campaigns/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}

// ── Delete ──
function deleteCampaign(id) {
    if (!confirm('Delete this campaign?')) return;
    fetch(`/merchant/api/campaigns/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>
@endsection
