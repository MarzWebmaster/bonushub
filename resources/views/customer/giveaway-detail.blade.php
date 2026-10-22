@extends('layouts.app')
@section('title', $campaign->title . ' — Giveaway')
@section('content')
<div class="page-container" style="padding-top:0" x-data="giveawayDetail({{ filled($campaign->terms) ? 'true' : 'false' }})">
    <div class="page-header flex-col sm:flex-row gap-3">
        <a href="{{ route('customer.giveaways') }}" class="btn btn-secondary text-sm">← Back</a>
        <div>
            <h1 class="page-title">🎉 {{ $campaign->title }}</h1>
            <p class="page-subtitle">by {{ $campaign->merchant->name ?? $campaign->merchant->company_name ?? 'Merchant' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content (2 cols) --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Prize Card --}}
            <div class="rounded-2xl bg-gradient-to-br from-bonus-600 to-purple-600 text-white p-6 sm:p-8 text-center shadow-lg">
                <div class="text-5xl mb-3">🏆</div>
                <h2 class="text-xl sm:text-2xl font-bold mb-1">{{ $campaign->prize_description }}</h2>
                @if($campaign->prize_value)
                    <div class="text-sm opacity-90">Worth {{ number_format($campaign->prize_value) }} points</div>
                @endif
                <div class="text-xs opacity-80 mt-2">
                    {{ $campaign->winner_count }} winner(s) • {{ $campaign->selectionLabel() }}
                </div>
            </div>

            {{-- Description --}}
            @if($campaign->description)
                <div class="card p-4 sm:p-5">
                    <h2 class="font-semibold text-sm mb-3">📋 About This Giveaway</h2>
                    <div class="text-sm text-surface-600 leading-relaxed">{!! nl2br(e($campaign->description)) !!}</div>
                </div>
            @endif

            {{-- Rules & T&C --}}
            <div class="card p-4 sm:p-5">
                <h2 class="font-semibold text-sm mb-3">📜 How to win</h2>
                <div class="text-sm text-surface-600 leading-relaxed">
                    @if($campaign->selection_method === 'threshold')
                        Bring in <strong>{{ $campaign->threshold_score }}</strong> friends who join and become active. Everyone who reaches it can win (max {{ $campaign->winner_count }}, best score first).
                    @elseif($campaign->selection_method === 'milestone')
                        The more friends you bring, the better the prize:
                        <ul class="list-disc ml-5 mt-1">
                            @foreach(array_reverse($campaign->milestoneLadder()) as $m)
                                <li><strong>{{ $m['score'] }}</strong> friends → {{ $m['prize'] }}</li>
                            @endforeach
                        </ul>
                    @elseif($campaign->selection_method === 'top_referrers')
                        The {{ $campaign->winner_count }} people who bring in the most friends win.
                    @else
                        Winners are chosen by the merchant.
                    @endif
                    <p class="text-xs text-surface-400 mt-2">A friend counts once they have joined through your link and become active (a few days after joining). Ties go to whoever entered first. No random draw.</p>
                </div>
                @if($campaign->terms)
                    <details class="mt-3 text-sm"><summary class="cursor-pointer font-medium">Terms &amp; Conditions</summary>
                        <div class="mt-2 text-surface-600">{!! nl2br(e($campaign->terms)) !!}</div></details>
                @endif
            </div>

            {{-- Leaderboard --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between px-4 pt-4 pb-2">
                    <h2 class="font-semibold text-sm">🏆 Leaderboard</h2>
                    <span class="text-xs text-surface-400" x-text="leaderboard.length + ' participants'"></span>
                </div>
                @if($leaderboard->isEmpty())
                    <div class="text-center py-10 text-surface-400">
                        <div class="text-4xl mb-2">📝</div>
                        <p class="text-sm">No entries yet. Be the first!</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="data-table text-sm">
                            <thead>
                                <tr>
                                    <th class="w-14 text-center">Rank</th>
                                    <th>Participant</th>
                                    <th class="text-center">Referrals</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $i => $entry)
                                <tr @if($myEntry && $entry->customer_id === $myEntry->customer_id) class="bg-bonus-50" @endif>
                                    <td class="text-center text-base">
                                        @if($i === 0)🥇
                                        @elseif($i === 1)🥈
                                        @elseif($i === 2)🥉
                                        @else #{{ $i + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ $entry->customer->name ?? 'Unknown' }}</span>
                                        @if($myEntry && $entry->customer_id === $myEntry->customer_id)
                                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-bonus-100 text-bonus-700 rounded">You</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="font-bold text-bonus-600">{{ $entry->score }}</span>
                                    </td>
                                    <td>
                                        @if($entry->is_winner)
                                            <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">🏆 Winner!</span>
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

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Entry Status --}}
            <div class="card p-5 text-center">
                @if($myEntry)
                    <div class="text-4xl mb-2">✅</div>
                    <h3 class="text-lg font-bold text-green-600">You're Entered!</h3>
                    <p class="text-sm text-surface-500 mt-1">{{ $myEntry->score }} valid referral(s)</p>
                    @if($campaign->selection_method === 'threshold' && $campaign->threshold_score)
                        <p class="text-xs text-surface-400 mt-1">{{ max(0, $campaign->threshold_score - $myEntry->score) }} more to reach the target ({{ $campaign->threshold_score }})</p>
                    @elseif($campaign->selection_method === 'milestone')
                        @php($next = collect($campaign->milestoneLadder())->sortBy('score')->first(fn($m) => $m['score'] > $myEntry->score))
                        @if($next)<p class="text-xs text-surface-400 mt-1">{{ $next['score'] - $myEntry->score }} more for: {{ $next['prize'] }}</p>@endif
                    @endif
                @elseif($campaign->status === 'active')
                    <div class="text-4xl mb-2">📝</div>
                    <h3 class="text-lg font-bold mb-3">Join Giveaway</h3>
                    @if($campaign->terms)
                        <label class="flex items-start gap-2 text-left text-xs text-surface-600 mb-3">
                            <input type="checkbox" x-model="accepted" class="mt-0.5">
                            <span>I have read and agree to the Terms &amp; Conditions of this giveaway.</span>
                        </label>
                    @endif
                    <button class="btn btn-primary w-full py-3 text-base" @click="enter()" :disabled="entering || (hasTerms && !accepted)">
                        <span x-show="!entering">🎉 Enter Now</span>
                        <span x-show="entering" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Entering...
                        </span>
                    </button>
                    <div x-show="enterError" class="text-red-500 text-xs mt-2" x-text="enterError"></div>
                    <div x-show="enterSuccess" class="text-green-600 text-xs mt-2" x-text="enterSuccess"></div>
                @else
                    <div class="text-4xl mb-2">⏹</div>
                    <h3 class="text-lg font-bold text-surface-400">Giveaway Ended</h3>
                @endif
            </div>

            {{-- Share box (entrants only, while the giveaway runs) --}}
            @if(!empty($shareUrl))
                @php($mName = $campaign->merchant->company_name ?? 'merchant')
                @php($caps = [
                    'Malay'   => "🎉 Jom sertai giveaway {$campaign->title} oleh {$mName}! Hadiah: {$campaign->prize_description}. Daftar guna pautan saya: {$shareUrl}",
                    'English' => "🎁 Join the {$campaign->title} giveaway by {$mName}! Prize: {$campaign->prize_description}. Sign up with my link: {$shareUrl}",
                    'Short'   => "Menang {$campaign->prize_description}! Sertai di sini 👉 {$shareUrl}",
                ])
                <div class="card p-4 sm:p-5" id="share-box">
                    <h2 class="font-semibold text-sm mb-1">📣 Share and climb the leaderboard</h2>
                    <p class="text-xs text-surface-500 mb-3">Every friend who joins through your link and becomes active adds to your score.</p>
                    <div class="flex gap-2 mb-4">
                        <input type="text" readonly id="share-link" value="{{ $shareUrl }}" class="form-input text-xs flex-1">
                        <button type="button" class="btn btn-secondary text-xs" onclick="copyText('share-link', this)">Copy link</button>
                    </div>
                    @foreach($caps as $label => $text)
                        <div class="mb-3">
                            <div class="text-xs font-medium text-surface-600 mb-1">{{ $label }} caption</div>
                            <textarea readonly rows="3" id="cap-{{ $loop->index }}" class="form-input text-xs w-full">{{ $text }}</textarea>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <button type="button" class="btn btn-secondary text-xs" onclick="copyText('cap-{{ $loop->index }}', this)">Copy</button>
                                <a class="btn btn-secondary text-xs" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($text) }}">WhatsApp</a>
                                <a class="btn btn-secondary text-xs" target="_blank" rel="noopener" href="https://t.me/share/url?url={{ rawurlencode($shareUrl) }}&text={{ rawurlencode($text) }}">Telegram</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <script>
                function copyText(id, btn) {
                    const el = document.getElementById(id);
                    const done = () => { const o = btn.textContent; btn.textContent = 'Copied ✔'; setTimeout(() => btn.textContent = o, 1500); };
                    if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(el.value).then(done); }
                    else { el.select(); document.execCommand('copy'); done(); }
                }
                </script>
            @endif

            {{-- My prize --}}
            @php($myWin = $myEntry ? $campaign->winners->where('customer_id', $myEntry->customer_id)->where('status', '!=', 'forfeited')->first() : null)
            @if($myWin)
                <div class="card p-5 text-center" style="border:2px solid #fde68a">
                    <div class="text-4xl mb-2">🏆</div>
                    <h3 class="text-lg font-bold">You won!</h3>
                    <p class="text-sm text-surface-600 mt-1">{{ $myWin->prize_description }}</p>
                    @if($myWin->status === 'claimed')
                        <p class="text-xs text-green-600 mt-2">Prize handed over ✔</p>
                    @elseif($myWin->claim_deadline_at)
                        <p class="text-xs text-surface-500 mt-2">Claim by {{ $myWin->claim_deadline_at->format('d/m/Y') }}. The merchant will contact you.</p>
                    @endif
                </div>
            @endif

            {{-- Campaign Info --}}
            <div class="card p-4">
                <h2 class="font-semibold text-sm mb-3">📊 Info</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-surface-500">Status</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $campaign->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-surface-100 text-surface-600' }}">{{ ucfirst($campaign->status) }}</span></div>
                    <div class="flex justify-between"><span class="text-surface-500">Total Entries</span>
                        <span class="font-semibold">{{ number_format($campaign->entries->sum('entry_count')) }}</span></div>
                    <div class="flex justify-between"><span class="text-surface-500">Participants</span>
                        <span class="font-semibold">{{ $campaign->participantCount() }}</span></div>
                    <div class="flex justify-between"><span class="text-surface-500">Winners</span>
                        <span class="font-semibold">{{ $campaign->winner_count }}</span></div>
                    <div class="flex justify-between"><span class="text-surface-500">Ends</span>
                        <span class="text-xs">{{ $campaign->ends_at ? $campaign->ends_at->format('M j, Y g:i A') : 'No end' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function giveawayDetail(hasTerms = false) {
    return {
        hasTerms, accepted: false,
        entering: false, enterError: '', enterSuccess: '',
        async enter() {
            this.entering = true; this.enterError = ''; this.enterSuccess = '';
            try {
                const res = await fetch('/customer/giveaways/{{ $campaign->id }}/enter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ accept_terms: this.accepted }),
                });
                const data = await res.json();
                if (data.success) { this.enterSuccess = data.message; setTimeout(() => location.reload(), 1000); }
                else { this.enterError = data.message; }
            } catch (e) { this.enterError = 'Network error. Please try again.'; }
            finally { this.entering = false; }
        }
    };
}
</script>
@endsection
