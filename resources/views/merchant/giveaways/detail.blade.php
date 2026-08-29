@extends('layouts.app')
@section('title', $campaign->title . ' — Giveaway')
@section('content')
<div class="page-container" style="padding-top:0" x-data="giveawayDetail()">
    <div class="page-header">
        <a href="{{ route('merchant.giveaways.index') }}" class="btn btn-secondary">← Back</a>
        <div style="flex:1">
            <h1 class="page-title">🎉 {{ $campaign->title }}</h1>
            <p class="page-subtitle">{{ $campaign->prize_description }}</p>
        </div>
        <div style="display:flex;gap:8px">
            @if($campaign->status === 'draft')
                <button class="btn btn-success" @click="activate()">🚀 Activate</button>
            @endif
            @if($campaign->status === 'active')
                <button class="btn btn-warning" @click="end()">⏹ End Campaign</button>
                <button class="btn btn-primary" @click="showSelectModal = true">🏆 Select Winners</button>
            @endif
        </div>
    </div>

    <!-- Campaign Info -->
    <div class="stats-grid" style="grid-template-columns:repeat(5,1fr)">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--primary-light);color:var(--primary)"><span>📝</span></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($campaign->entries_count) }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--success-light);color:var(--success)"><span>👥</span></div>
            <div class="stat-info">
                <div class="stat-value">{{ $campaign->participantCount() }}</div>
                <div class="stat-label">Participants</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-light);color:var(--warning)"><span>🏆</span></div>
            <div class="stat-info">
                <div class="stat-value">{{ $campaign->winners()->count() }}</div>
                <div class="stat-label">Winners</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--danger-light);color:var(--danger)"><span>📊</span></div>
            <div class="stat-info">
                <div class="stat-value">{{ ucfirst($campaign->selection_method) }}</div>
                <div class="stat-label">Selection</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--info-light);color:var(--info)"><span>⏰</span></div>
            <div class="stat-info">
                <div class="stat-value" style="font-size:14px">
                    {{ $campaign->ends_at ? $campaign->ends_at->format('M j, Y') : 'No end' }}
                </div>
                <div class="stat-label">Ends At</div>
            </div>
        </div>
    </div>

    @if($campaign->description)
        <div class="card" style="margin-bottom:24px">
            <div class="card-body" style="color:var(--text-secondary);line-height:1.6">
                {!! nl2br(e($campaign->description)) !!}
            </div>
        </div>
    @endif

    <!-- Winners Section -->
    @if($campaign->winners->count() > 0)
        <div class="card" style="margin-bottom:24px;border:2px solid var(--warning)">
            <div class="card-header">
                <h2 class="card-title">🏆 Winners ({{ $campaign->winners->count() }})</h2>
                <span class="badge badge-success">Announced {{ $campaign->winners_announced_at?->format('M j, g:i A') }}</span>
            </div>
            <div class="card-body" style="padding:0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Winner</th>
                            <th>Entries</th>
                            <th>Prize</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaign->winners->sortBy('position') as $w)
                        <tr style="background:var(--warning-light)">
                            <td>
                                @if($w->position === 1)🥇
                                @elseif($w->position === 2)🥈
                                @elseif($w->position === 3)🥉
                                @else #{{ $w->position }}
                                @endif
                            </td>
                            <td style="font-weight:600;color:var(--text-primary)">
                                {{ $w->customer->name ?? 'Unknown' }}
                            </td>
                            <td>{{ $w->entry?->entry_count ?? '-' }} entries</td>
                            <td style="color:var(--warning);font-weight:500">{{ $w->prize_description }}</td>
                            <td>
                                <span class="badge {{ $w->status === 'claimed' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $w->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Leaderboard -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🏆 Leaderboard (Top 20)</h2>
        </div>
        @if($leaderboard->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h3 class="empty-state-title">No entries yet</h3>
                <p class="empty-state-text">Share your giveaway to get participants!</p>
            </div>
        @else
            <div class="card-body" style="padding:0">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:60px">Rank</th>
                            <th>Customer</th>
                            <th>Entries</th>
                            <th>Won?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $i => $entry)
                        <tr>
                            <td>
                                @if($i === 0)🥇
                                @elseif($i === 1)🥈
                                @elseif($i === 2)🥉
                                @else <span style="color:var(--text-muted);font-weight:600">#{{ $i + 1 }}</span>
                                @endif
                            </td>
                            <td style="font-weight:500;color:var(--text-primary)">
                                {{ $entry->customer->name ?? 'Unknown' }}
                            </td>
                            <td>
                                <span style="font-weight:700;color:var(--primary)">{{ $entry->entry_count }}</span>
                                <span style="color:var(--text-muted)"> entries</span>
                            </td>
                            <td>
                                @if($entry->is_winner)
                                    <span class="badge badge-success">🏆 Winner</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Winner Selection Modal -->
    <div x-show="showSelectModal" x-cloak class="modal-overlay" @click.self="showSelectModal = false"
         style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;display:flex;align-items:center;justify-content:center">
        <div style="background:white;border-radius:16px;padding:32px;max-width:500px;width:90%">
            <h3 style="font-size:20px;font-weight:700;margin:0 0 8px">🏆 Select Winners</h3>
            <p style="font-size:14px;color:var(--text-secondary);margin:0 0 20px">
                Choose how to select the {{ $campaign->winner_count }} winner(s)
            </p>

            <div style="display:flex;flex-direction:column;gap:12px">
                @if($campaign->selection_method === 'random')
                    <button class="btn btn-primary btn-lg" @click="selectWinners('random')"
                            :disabled="selecting">
                        🎲 Random Draw
                    </button>
                @endif
                @if($campaign->selection_method === 'top_referrers')
                    <button class="btn btn-primary btn-lg" @click="selectWinners('top_referrers')"
                            :disabled="selecting">
                        🏆 Select Top Referrers
                    </button>
                @endif
                @if($campaign->selection_method === 'manual')
                    <div style="color:var(--text-secondary);font-size:14px;padding:12px;background:var(--bg)">
                        Manual selection requires API call. Coming soon.
                    </div>
                @endif
            </div>

            <div x-show="selectError" class="alert alert-error" style="margin-top:12px" x-text="selectError"></div>
            <div x-show="selectSuccess" class="alert alert-success" style="margin-top:12px" x-text="selectSuccess"></div>

            <div style="display:flex;gap:8px;margin-top:20px;justify-content:flex-end">
                <button class="btn btn-secondary" @click="showSelectModal = false">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function giveawayDetail() {
    return {
        showSelectModal: false,
        selecting: false,
        selectError: '',
        selectSuccess: '',

        async activate() {
            if (!confirm('Activate this giveaway campaign?')) return;
            try {
                const res = await fetch('/merchant/giveaways/{{ $campaign->id }}/activate', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) { location.reload(); } else { alert(data.message); }
            } catch (e) { alert('Error activating campaign.'); }
        },

        async end() {
            if (!confirm('End this campaign? No new entries will be accepted.')) return;
            try {
                const res = await fetch('/merchant/giveaways/{{ $campaign->id }}/end', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) { location.reload(); } else { alert(data.message); }
            } catch (e) { alert('Error ending campaign.'); }
        },

        async selectWinners(method) {
            this.selecting = true;
            this.selectError = '';
            this.selectSuccess = '';
            try {
                const res = await fetch('/merchant/giveaways/{{ $campaign->id }}/select-winners', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ method })
                });
                const data = await res.json();
                if (data.success) {
                    this.selectSuccess = data.message;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.selectError = data.message;
                }
            } catch (e) {
                this.selectError = 'Error selecting winners.';
            } finally {
                this.selecting = false;
            }
        }
    };
}
</script>
@endsection