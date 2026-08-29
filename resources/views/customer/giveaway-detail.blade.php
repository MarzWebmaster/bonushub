@extends('layouts.app')
@section('title', $campaign->title . ' — Giveaway')
@section('content')
<div class="page-container" style="padding-top:0" x-data="giveawayDetail()">
    <div class="page-header">
        <a href="{{ route('customer.giveaways') }}" class="btn btn-secondary">← Back</a>
        <div>
            <h1 class="page-title">🎉 {{ $campaign->title }}</h1>
            <p class="page-subtitle">by {{ $campaign->merchant->name ?? $campaign->merchant->company_name ?? 'Merchant' }}</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
        <!-- Main Content -->
        <div>
            <!-- Prize Card -->
            <div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,var(--primary),#8b5cf6);color:white;border:none">
                <div class="card-body" style="text-align:center;padding:32px">
                    <div style="font-size:48px;margin-bottom:12px">🏆</div>
                    <h2 style="font-size:24px;font-weight:700;margin:0 0 8px;color:white">{{ $campaign->prize_description }}</h2>
                    @if($campaign->prize_value)
                        <div style="font-size:16px;opacity:0.9">Worth {{ number_format($campaign->prize_value) }} points</div>
                    @endif
                    <div style="font-size:14px;opacity:0.8;margin-top:12px">
                        {{ $campaign->winner_count }} winner(s) • {{ ucfirst($campaign->selection_method) }} selection
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($campaign->description)
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h2 class="card-title">📋 About This Giveaway</h2></div>
                    <div class="card-body" style="color:var(--text-secondary);line-height:1.8">
                        {!! nl2br(e($campaign->description)) !!}
                    </div>
                </div>
            @endif

            <!-- Leaderboard -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🏆 Leaderboard</h2>
                    <span style="font-size:13px;color:var(--text-muted)" x-text="leaderboard.length + ' participants'"></span>
                </div>
                @if($leaderboard->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <p class="empty-state-text">No entries yet. Be the first!</p>
                    </div>
                @else
                    <div class="card-body" style="padding:0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width:60px">Rank</th>
                                    <th>Participant</th>
                                    <th>Entries</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $i => $entry)
                                <tr @if($myEntry && $entry->customer_id === $myEntry->customer_id) style="background:var(--primary-light)" @endif>
                                    <td>
                                        @if($i === 0)🥇
                                        @elseif($i === 1)🥈
                                        @elseif($i === 2)🥉
                                        @else #{{ $i + 1 }}
                                        @endif
                                    </td>
                                    <td style="font-weight:500;color:var(--text-primary)">
                                        {{ $entry->customer->name ?? 'Unknown' }}
                                        @if($myEntry && $entry->customer_id === $myEntry->customer_id)
                                            <span class="badge badge-primary" style="font-size:10px;margin-left:4px">You</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-weight:700;color:var(--primary)">{{ $entry->entry_count }}</span>
                                        <span style="color:var(--text-muted)"> entries</span>
                                    </td>
                                    <td>
                                        @if($entry->is_winner)
                                            <span class="badge badge-success">🏆 Winner!</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Entry Status -->
            <div class="card" style="margin-bottom:24px">
                <div class="card-body" style="text-align:center">
                    @if($myEntry)
                        <div style="font-size:32px;margin-bottom:8px">✅</div>
                        <h3 style="font-size:18px;font-weight:700;color:var(--success);margin:0">You're Entered!</h3>
                        <p style="font-size:14px;color:var(--text-secondary);margin:8px 0 0">
                            {{ $myEntry->entry_count }} entries
                        </p>
                    @elseif($campaign->status === 'active')
                        <div style="font-size:32px;margin-bottom:8px">📝</div>
                        <h3 style="font-size:18px;font-weight:700;margin:0 0 12px">Join Giveaway</h3>
                        <button class="btn btn-primary btn-lg" style="width:100%"
                                @click="enter()" :disabled="entering">
                            <span x-show="!entering">🎉 Enter Now</span>
                            <span x-show="entering">Entering...</span>
                        </button>
                        <div x-show="enterError" style="color:var(--danger);font-size:13px;margin-top:8px" x-text="enterError"></div>
                        <div x-show="enterSuccess" style="color:var(--success);font-size:13px;margin-top:8px" x-text="enterSuccess"></div>
                    @else
                        <div style="font-size:32px;margin-bottom:8px">⏹</div>
                        <h3 style="font-size:18px;font-weight:700;color:var(--text-muted);margin:0">Giveaway Ended</h3>
                    @endif
                </div>
            </div>

            <!-- Campaign Info -->
            <div class="card">
                <div class="card-header"><h2 class="card-title">📊 Info</h2></div>
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                        <span style="font-size:13px;color:var(--text-secondary)">Status</span>
                        <span class="badge badge-{{ $campaign->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($campaign->status) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                        <span style="font-size:13px;color:var(--text-secondary)">Total Entries</span>
                        <span style="font-weight:600;color:var(--text-primary)">{{ number_format($campaign->entries->sum('entry_count')) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                        <span style="font-size:13px;color:var(--text-secondary)">Participants</span>
                        <span style="font-weight:600;color:var(--text-primary)">{{ $campaign->participantCount() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                        <span style="font-size:13px;color:var(--text-secondary)">Winners</span>
                        <span style="font-weight:600;color:var(--text-primary)">{{ $campaign->winner_count }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0">
                        <span style="font-size:13px;color:var(--text-secondary)">Ends</span>
                        <span style="font-size:13px;color:var(--text-primary)">
                            {{ $campaign->ends_at ? $campaign->ends_at->format('M j, Y g:i A') : 'No end' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function giveawayDetail() {
    return {
        entering: false,
        enterError: '',
        enterSuccess: '',

        async enter() {
            this.entering = true;
            this.enterError = '';
            this.enterSuccess = '';

            try {
                const res = await fetch('/customer/giveaways/{{ $campaign->id }}/enter', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                if (data.success) {
                    this.enterSuccess = data.message;
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.enterError = data.message;
                }
            } catch (e) {
                this.enterError = 'Network error. Please try again.';
            } finally {
                this.entering = false;
            }
        }
    };
}
</script>
@endsection