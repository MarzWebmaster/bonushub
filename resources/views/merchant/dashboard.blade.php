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

    {{-- Row 1: Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card border-l-bonus-500">
            <div><p class="text-sm font-medium text-surface-500">Total Customers</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-total-customers">-</p></div>
            <div class="w-12 h-12 rounded-xl bg-bonus-50 flex items-center justify-center"><svg class="w-6 h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-emerald-500">
            <div><p class="text-sm font-medium text-surface-500">Points Awarded</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-points">-</p></div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-amber-500">
            <div><p class="text-sm font-medium text-surface-500">Pending Approvals</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-pending">-</p></div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card border-l-purple-500">
            <div><p class="text-sm font-medium text-surface-500">Products</p><p class="text-3xl font-bold text-surface-800 mt-1" id="m-products">-</p></div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
    </div>

    {{-- Row 2: Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        {{-- Registered Customers Chart --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-700 mb-3">Registered Customers</h3>
            <canvas id="chart-registrations" height="200"></canvas>
        </div>

        {{-- Points Earned Chart --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-700 mb-3">Points Earned</h3>
            <canvas id="chart-earned" height="200"></canvas>
        </div>

        {{-- Points Redeemed Chart --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-700 mb-3">Points Redeemed</h3>
            <canvas id="chart-redeemed" height="200"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
fetch('/merchant/api/dashboard').then(r=>r.json()).then(d=>{
    if(!d.success) return;

    // Update stat cards
    document.getElementById('m-total-customers').textContent = d.total_customers;
    document.getElementById('m-points').textContent = d.total_points.toLocaleString();
    document.getElementById('m-pending').textContent = d.pending_approvals;
    document.getElementById('m-products').textContent = d.total_products;

    if(!d.chart || !d.chart.labels.length) return;

    const labels = d.chart.labels;
    const gridColor = 'rgba(148,163,184,0.15)';
    const tickColor = '#94a3b8';

    const baseOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } }, beginAtZero: true }
        }
    };

    // Registrations - bar chart
    new Chart(document.getElementById('chart-registrations'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: d.chart.registrations,
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderRadius: 6,
                barThickness: 28
            }]
        },
        options: baseOpts
    });

    // Earned - line chart
    new Chart(document.getElementById('chart-earned'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: d.chart.earned,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#10b981'
            }]
        },
        options: baseOpts
    });

    // Redeemed - line chart
    new Chart(document.getElementById('chart-redeemed'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: d.chart.redeemed,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#f59e0b'
            }]
        },
        options: baseOpts
    });
});
</script>
@endpush
@endsection
