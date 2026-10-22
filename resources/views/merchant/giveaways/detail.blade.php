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
            @endif
            @if($campaign->winners->count() === 0 && $campaign->readyForDraw())
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
                <div class="stat-value" style="font-size:14px">{{ $campaign->selectionLabel() }}</div>
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

    <div class="card" style="margin-bottom:24px">
        <div class="card-body" style="font-size:14px;line-height:1.6">
            <strong>Rules:</strong>
            @if($campaign->selection_method === 'threshold')
                reach a score of {{ $campaign->threshold_score }} (valid referrals); best score first, max {{ $campaign->winner_count }} winners.
            @elseif($campaign->selection_method === 'milestone')
                @foreach(array_reverse($campaign->milestoneLadder()) as $m) score {{ $m['score'] }} → {{ $m['prize'] }}@if(!$loop->last); @endif @endforeach
            @elseif($campaign->selection_method === 'top_referrers')
                top {{ $campaign->winner_count }} by referral score (at least 1).
            @else
                chosen manually by you, with a recorded reason.
            @endif
            <br><span style="color:var(--text-muted)">Score counts referrals that were validated (engaged customers) and signed up between start and end. Validation takes a few days, so draw a few days after the end.</span>
            @if($campaign->terms)
                <details style="margin-top:8px"><summary style="cursor:pointer">Terms &amp; Conditions</summary><div style="margin-top:6px">{!! nl2br(e($campaign->terms)) !!}</div></details>
            @else
                <div style="margin-top:6px;color:var(--warning)">⚠ No T&amp;C on this (older) campaign.</div>
            @endif
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
                            <th>Claim by</th>
                            <th></th>
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
                                <span class="badge {{ $w->status === 'claimed' ? 'badge-success' : ($w->status === 'forfeited' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $w->status }}
                                </span>
                                @if($w->status === 'pending' && $w->claim_deadline_at && $w->claim_deadline_at->isPast())
                                    <span class="badge badge-danger">overdue</span>
                                @endif
                            </td>
                            <td style="font-size:12px">{{ $w->claim_deadline_at ? $w->claim_deadline_at->format('d/m/Y') : '-' }}</td>
                            <td style="white-space:nowrap">
                                @if($w->status === 'pending')
                                    <button class="btn btn-success" style="padding:2px 8px;font-size:12px" @click="claimWinner({{ $w->id }})">✔ Claimed</button>
                                    @if($campaign->selection_method === 'manual')
                                        <select id="rep-{{ $w->id }}" class="form-input" style="width:auto;display:inline-block;font-size:12px">
                                            <option value="">Replace with…</option>
                                            @foreach($campaign->entries->whereNotIn('customer_id', $campaign->winners->pluck('customer_id')) as $cand)
                                                <option value="{{ $cand->customer_id }}">{{ $cand->customer->name ?? 'Unknown' }} (score {{ $cand->score }})</option>
                                            @endforeach
                                        </select>
                                        <input id="reason-{{ $w->id }}" class="form-input" style="width:140px;display:inline-block;font-size:12px" placeholder="Reason (min 10)">
                                    @endif
                                    <button class="btn btn-warning" style="padding:2px 8px;font-size:12px" @click="replaceWinner({{ $w->id }}, {{ $campaign->selection_method === 'manual' ? 'true' : 'false' }})">↺ Replace</button>
                                @endif
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
                            <th>Referrals (score)</th>
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
                            <td style="font-weight:700">{{ $entry->score }}</td>
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
                @if($campaign->selection_method !== 'manual')
                    <button class="btn btn-primary btn-lg" @click="selectWinners()" :disabled="selecting">
                        🏆 Draw winners by rule ({{ $campaign->selectionLabel() }})
                    </button>
                @else
                    <div style="max-height:200px;overflow:auto;border:1px solid var(--border,#ddd);border-radius:8px;padding:8px">
                        @foreach($campaign->entries->sortByDesc('score') as $e)
                            <label style="display:flex;gap:8px;align-items:center;padding:3px 0">
                                <input type="checkbox" value="{{ $e->customer_id }}" x-model="picked">
                                <span>{{ $e->customer->name ?? 'Unknown' }} — score {{ $e->score }}</span>
                            </label>
                        @endforeach
                    </div>
                    <textarea class="form-input" rows="2" x-model="reason" placeholder="Reason for choosing these winners (required, min 10 chars)"></textarea>
                    <button class="btn btn-primary btn-lg" @click="selectWinners()" :disabled="selecting || picked.length === 0">🏆 Confirm winners</button>
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
        picked: [],
        reason: '',

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

        async claimWinner(id) {
            if (!confirm('Mark this prize as handed over?')) return;
            try {
                const res = await fetch('/merchant/giveaways/{{ $campaign->id }}/winners/' + id + '/claim', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) { location.reload(); } else { alert(data.message); }
            } catch (e) { alert('Error.'); }
        },

        async replaceWinner(id, manual) {
            const body = {};
            if (manual) {
                body.customer_id = parseInt(document.getElementById('rep-' + id).value || '0', 10) || null;
                body.reason = document.getElementById('reason-' + id).value;
            }
            if (!confirm('Forfeit this winner and pick a replacement?')) return;
            try {
                const res = await fetch('/merchant/giveaways/{{ $campaign->id }}/winners/' + id + '/replace', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (data.success) { location.reload(); } else { alert(data.message || 'Could not replace.'); }
            } catch (e) { alert('Error.'); }
        },

        async selectWinners() {
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
                    body: JSON.stringify({ method: '{{ $campaign->selection_method }}', winner_ids: this.picked.map(Number), reason: this.reason })
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