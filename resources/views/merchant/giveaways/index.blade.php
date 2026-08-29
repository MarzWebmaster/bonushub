@extends('layouts.app')
@section('title', 'Giveaway Campaigns')
@section('content')
<div class="page-container" style="padding-top:0" x-data="giveawayList()">
    <div class="page-header">
        <div>
            <h1 class="page-title">🎉 Giveaway Campaigns</h1>
            <p class="page-subtitle">Create viral giveaways to boost customer engagement</p>
        </div>
        <a href="{{ route('merchant.giveaways.create') }}" class="btn btn-primary btn-lg">
            <span>➕ New Campaign</span>
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--primary-light);color:var(--primary)">
                <span>🎉</span>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_campaigns'] }}</div>
                <div class="stat-label">Total Campaigns</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--success-light);color:var(--success)">
                <span>🟢</span>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['active_campaigns'] }}</div>
                <div class="stat-label">Active Now</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-light);color:var(--warning)">
                <span>📝</span>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['total_entries']) }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--danger-light);color:var(--danger)">
                <span>👥</span>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['total_participants']) }}</div>
                <div class="stat-label">Participants</div>
            </div>
        </div>
    </div>

    <!-- Campaign List -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Campaigns</h2>
        </div>
        @if($campaigns->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🎉</div>
                <h3 class="empty-state-title">No campaigns yet</h3>
                <p class="empty-state-text">Create your first giveaway to engage customers!</p>
            </div>
        @else
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Prize</th>
                            <th>Entries</th>
                            <th>Status</th>
                            <th>Ends</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $c)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:var(--text-primary)">{{ $c->title }}</div>
                                <div style="font-size:12px;color:var(--text-muted)">{{ $c->selection_method }} selection</div>
                            </td>
                            <td style="color:var(--text-primary);font-weight:500">{{ $c->prize_description }}</td>
                            <td>
                                <span style="font-weight:600;color:var(--primary)">{{ number_format($c->entries_count) }}</span>
                                @if($c->max_entries)
                                    <span style="color:var(--text-muted)">/ {{ number_format($c->max_entries) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($c->status === 'active')
                                    <span class="badge badge-success">🟢 Active</span>
                                @elseif($c->status === 'draft')
                                    <span class="badge badge-warning">📝 Draft</span>
                                @elseif($c->status === 'ended')
                                    <span class="badge badge-secondary">⏹ Ended</span>
                                @else
                                    <span class="badge badge-secondary">{{ $c->status }}</span>
                                @endif
                            </td>
                            <td style="font-size:13px;color:var(--text-secondary)">
                                {{ $c->ends_at ? $c->ends_at->format('M j, Y') : 'No end' }}
                            </td>
                            <td>
                                <a href="{{ route('merchant.giveaways.detail', $c->id) }}" class="btn btn-secondary" style="font-size:12px;padding:4px 10px">
                                    View →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function giveawayList() { return {}; }
</script>
@endsection