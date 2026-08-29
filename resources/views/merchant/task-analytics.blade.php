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

    <!-- Stat Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:24px">
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">📋 Total Tasks</div>
            <div style="font-size:28px;font-weight:700;color:#1e293b" x-text="data.total_tasks || 0"></div>
        </div>
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">✅ Active Tasks</div>
            <div style="font-size:28px;font-weight:700;color:#16a34a" x-text="data.active_tasks || 0"></div>
        </div>
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">📝 Total Submissions</div>
            <div style="font-size:28px;font-weight:700;color:#2563eb" x-text="data.total_submissions || 0"></div>
        </div>
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">⭐ Approved</div>
            <div style="font-size:28px;font-weight:700;color:#16a34a" x-text="data.approved_submissions || 0"></div>
        </div>
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">📈 Conversion Rate</div>
            <div style="font-size:28px;font-weight:700;color:#6366f1" x-text="(data.conversion_rate || 0) + '%'"></div>
        </div>
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">💎 Points Spent</div>
            <div style="font-size:28px;font-weight:700;color:#ea580c" x-text="(data.total_points_spent || 0).toLocaleString()"></div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
        <!-- Top Tasks -->
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <h3 style="font-weight:600;margin-bottom:12px">🏆 Top Performing Tasks</h3>
            <template x-if="data.top_tasks && data.top_tasks.length > 0">
                <div>
                    <template x-for="(task, i) in data.top_tasks" :key="task.id">
                        <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #f3f4f6">
                            <div style="width:28px;height:28px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#6b7280"
                                x-text="'#' + (i+1)"></div>
                            <div style="flex:1">
                                <div style="font-weight:500;font-size:14px" x-text="task.title"></div>
                                <div style="font-size:12px;color:#6b7280" x-text="task.completions + ' completions'"></div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-weight:600;color:#ea580c" x-text="task.points_spent.toLocaleString() + ' pts'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!data.top_tasks || data.top_tasks.length === 0">
                <p style="color:#9ca3af;font-size:14px;padding:20px 0;text-align:center">No task data yet</p>
            </template>
        </div>

        <!-- Daily Submissions Chart -->
        <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e5e7eb">
            <h3 style="font-weight:600;margin-bottom:12px">📅 Submissions (30 days)</h3>
            <canvas id="dailyChart" height="200"></canvas>
        </div>
    </div>

    <!-- Loading -->
    <template x-if="loading">
        <div style="text-align:center;padding:40px;color:#6b7280">Loading analytics...</div>
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

        async init() {
            try {
                const resp = await fetch('/merchant/api/tasks/analytics', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                if (json.success) {
                    this.data = json.analytics;
                    this.$nextTick(() => this.renderChart());
                }
            } catch (e) {
                console.error('Analytics error:', e);
            } finally {
                this.loading = false;
            }
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
                    plugins: { legend: { position: 'bottom' } },
                    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
                }
            });
        }
    }
}
</script>
@endpush
