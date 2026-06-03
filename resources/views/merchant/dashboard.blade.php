@extends('layouts.app')
@section('title', 'Dashboard - Merchant')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Merchant Dashboard</h1>
            <p class="page-subtitle">Your shop at a glance</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card border-l-bonus-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Total Customers</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1" id="m-total-customers">-</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-bonus-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-emerald-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Points Awarded</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1" id="m-points">-</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-amber-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Pending Approvals</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1" id="m-pending">-</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-purple-500">
            <div><p class="text-xs sm:text-sm font-medium text-surface-500">Products</p><p class="text-2xl sm:text-3xl font-bold text-surface-800 mt-1" id="m-products">-</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-50 flex items-center justify-center"><svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">

        {{-- Registered Customers (switchable) --}}
        <div class="card p-3 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-surface-700">Registered Customers</h3>
                <select id="reg-chart-type" class="text-xs border border-surface-200 rounded-lg px-2 py-1 bg-white text-surface-600 focus:outline-none focus:ring-2 focus:ring-bonus-400 w-full sm:w-auto">
                    <option value="bar">Bar</option>
                    <option value="line">Line</option>
                    <option value="doughnut">Doughnut</option>
                    <option value="pie">Pie</option>
                </select>
            </div>
            <div class="chart-wrap"><canvas id="chart-registrations"></canvas></div>
        </div>

        {{-- Points Earned vs Redeemed --}}
        <div class="card p-3 sm:p-5">
            <h3 class="text-xs sm:text-sm font-semibold text-surface-700 mb-3">Points Earned vs Redeemed</h3>
            <div class="chart-wrap"><canvas id="chart-points"></canvas></div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
let regChart = null;
let pointsChart = null;
let chartData = null;

const chartColors = {
    registrations: { bg: 'rgba(99,102,241,0.7)', border: '#6366f1' },
    earned: { bg: 'rgba(16,185,129,0.15)', border: '#10b981', point: '#10b981' },
    redeemed: { bg: 'rgba(245,158,11,0.15)', border: '#f59e0b', point: '#f59e0b' }
};

const doughnutColors = [
    '#6366f1','#8b5cf6','#a78bfa','#10b981','#f59e0b',
    '#ef4444','#3b82f6','#ec4899','#14b8a6','#f97316'
];

function isMobile() { return window.innerWidth < 640; }

function setChartHeights() {
    var h = isMobile() ? '200px' : '260px';
    document.querySelectorAll('.chart-wrap').forEach(function(el) { el.style.height = h; });
}

function buildRegChart(type) {
    if (regChart) regChart.destroy();
    var ctx = document.getElementById('chart-registrations').getContext('2d');
    var labels = chartData.chart.labels;
    var data = chartData.chart.registrations;
    var mobile = isMobile();

    if (type === 'bar' || type === 'line') {
        regChart = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: type === 'bar' ? chartColors.registrations.bg : chartColors.earned.bg,
                    borderColor: chartColors.registrations.border,
                    borderWidth: type === 'line' ? 2 : 0,
                    borderRadius: type === 'bar' ? 4 : 0,
                    barThickness: type === 'bar' ? (mobile ? 14 : 28) : undefined,
                    maxBarThickness: mobile ? 20 : 40,
                    fill: type === 'line',
                    tension: 0.4,
                    pointRadius: type === 'line' ? (mobile ? 3 : 5) : 0,
                    pointBackgroundColor: chartColors.registrations.border
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: mobile ? 9 : 11 } } },
                    y: { grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { color: '#94a3b8', font: { size: mobile ? 9 : 11 } }, beginAtZero: true }
                }
            }
        });
    } else {
        regChart = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: doughnutColors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#64748b', font: { size: mobile ? 9 : 11 }, padding: mobile ? 8 : 12, usePointStyle: true, pointStyle: 'circle' }
                    }
                }
            }
        });
    }
}

function buildPointsChart() {
    if (pointsChart) pointsChart.destroy();
    var ctx = document.getElementById('chart-points').getContext('2d');
    var mobile = isMobile();
    pointsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.chart.labels,
            datasets: [
                {
                    label: 'Earned',
                    data: chartData.chart.earned,
                    borderColor: chartColors.earned.border,
                    backgroundColor: chartColors.earned.bg,
                    fill: true,
                    tension: 0.4,
                    pointRadius: mobile ? 3 : 5,
                    pointBackgroundColor: chartColors.earned.point,
                    borderWidth: 2
                },
                {
                    label: 'Redeemed',
                    data: chartData.chart.redeemed,
                    borderColor: chartColors.redeemed.border,
                    backgroundColor: chartColors.redeemed.bg,
                    fill: true,
                    tension: 0.4,
                    pointRadius: mobile ? 3 : 5,
                    pointBackgroundColor: chartColors.redeemed.point,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { color: '#64748b', font: { size: mobile ? 10 : 12 }, padding: mobile ? 10 : 16, usePointStyle: true, pointStyle: 'circle' }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: mobile ? 9 : 11 } } },
                y: { grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { color: '#94a3b8', font: { size: mobile ? 9 : 11 } }, beginAtZero: true }
            }
        }
    });
}

function buildAll() {
    setChartHeights();
    if (chartData) {
        var type = document.getElementById('reg-chart-type').value;
        buildRegChart(type);
        buildPointsChart();
    }
}

fetch('/merchant/api/dashboard').then(function(r){return r.json()}).then(function(d){
    if(!d.success) return;
    document.getElementById('m-total-customers').textContent = d.total_customers;
    document.getElementById('m-points').textContent = d.total_points.toLocaleString();
    document.getElementById('m-pending').textContent = d.pending_approvals;
    document.getElementById('m-products').textContent = d.total_products;
    if(!d.chart || !d.chart.labels.length) return;
    chartData = d;
    buildAll();
});

document.getElementById('reg-chart-type').addEventListener('change', function(){
    if (chartData) buildRegChart(this.value);
});

window.addEventListener('resize', function(){
    if (chartData) buildAll();
});
</script>
@endpush
@endsection