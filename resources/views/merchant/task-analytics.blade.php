@extends('layouts.app')
@section('title', 'Task Analytics — Merchant')
@section('content')
<div class="page-container" style="padding-top:0" x-data="taskAnalytics()">
    <div class="page-header">
        <div>
            <h1 class="page-title">📊 Task Analytics</h1>
            <p class="page-subtitle">Viral engine performance overview</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <template x-for="(s, i) in stats" :key="i">
            <div class="bg-white rounded-xl p-4 border border-surface-200 hover:shadow-md transition-shadow">
                <div class="text-xs text-surface-500 mb-1" x-text="s.icon + ' ' + s.label"></div>
                <div class="text-2xl sm:text-3xl font-bold" :style="'color:' + s.color" x-text="s.value"></div>
            </div>
        </template>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Top Tasks --}}
        <div class="bg-white rounded-xl p-4 border border-surface-200">
            <h3 class="font-semibold text-sm mb-3">🏆 Top Performing Tasks</h3>
            <template x-if="data.top_tasks && data.top_tasks.length > 0">
                <div class="space-y-0">
                    <template x-for="(task, i) in data.top_tasks" :key="task.id">
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 last:border-0">
                            <div class="w-7 h-7 rounded-full bg-surface-100 flex items-center justify-center font-bold text-xs text-surface-500 flex-shrink-0"
                                x-text="'#' + (i+1)"></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate" x-text="task.title"></div>
                                <div class="text-xs text-surface-500" x-text="task.completions + ' completions'"></div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="font-semibold text-sm text-orange-600" x-text="task.points_spent.toLocaleString() + ' pts'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!data.top_tasks || data.top_tasks.length === 0">
                <div class="text-center py-8 text-surface-400 text-sm">No task data yet</div>
            </template>
        </div>

        {{-- Daily Submissions Chart --}}
        <div class="bg-white rounded-xl p-4 border border-surface-200">
            <h3 class="font-semibold text-sm mb-3">📅 Submissions (30 days)</h3>
            <canvas id="dailyChart" height="200"></canvas>
        </div>
    </div>

    {{-- Loading --}}
    <template x-if="loading">
        <div class="text-center py-10 text-surface-500">
            <svg class="w-8 h-8 animate-spin mx-auto mb-2 text-bonus-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            Loading analytics...
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function taskAnalytics() {
    return {
        data: {},
        loading: true,
        chart: null,
        stats: [],

        async init() {
            try {
                const resp = await fetch('/merchant/api/tasks/analytics', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                if (json.success) {
                    this.data = json.analytics;
                    this.buildStats();
                    this.$nextTick(() => this.renderChart());
                }
            } catch (e) {
                console.error('Analytics error:', e);
            } finally {
                this.loading = false;
            }
        },

        buildStats() {
            const d = this.data;
            this.stats = [
                { icon: '📋', label: 'Total Tasks', value: d.total_tasks || 0, color: '#1e293b' },
                { icon: '✅', label: 'Active', value: d.active_tasks || 0, color: '#16a34a' },
                { icon: '📝', label: 'Submissions', value: d.total_submissions || 0, color: '#2563eb' },
                { icon: '⭐', label: 'Approved', value: d.approved_submissions || 0, color: '#16a34a' },
                { icon: '📈', label: 'Conversion', value: (d.conversion_rate || 0) + '%', color: '#6366f1' },
                { icon: '💎', label: 'Points Spent', value: (d.total_points_spent || 0).toLocaleString(), color: '#ea580c' },
            ];
        },

        renderChart() {
            const canvas = document.getElementById('dailyChart');
            if (!canvas || !this.data.daily_data || this.data.daily_data.length === 0) return;

            const labels = this.data.daily_data.map(d => d.date);
            const pending = this.data.daily_data.map(d => d.pending);
            const approved = this.data.daily_data.map(d => d.approved);
            const rejected = this.data.daily_data.map(d => d.rejected);

            this.chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Approved', data: approved, backgroundColor: '#16a34a', borderRadius: 4 },
                        { label: 'Pending', data: pending, backgroundColor: '#f59e0b', borderRadius: 4 },
                        { label: 'Rejected', data: rejected, backgroundColor: '#ef4444', borderRadius: 4 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
                    scales: {
                        x: { stacked: true, ticks: { font: { size: 10 }, maxRotation: 45 } },
                        y: { stacked: true, beginAtZero: true, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    }
}
</script>
@endpush
