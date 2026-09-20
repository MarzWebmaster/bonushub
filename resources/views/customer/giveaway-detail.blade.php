@extends('layouts.app')
@section('title', $campaign->title . ' — Giveaway')
@section('content')
<div class="page-container" style="padding-top:0" x-data="giveawayDetail()">
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
                @endif>
                <div class="text-xs opacity-80 mt-2">
                    {{ $campaign->winner_count }} winner(s) • {{ ucfirst($campaign->selection_method) }} selection
                </div>
            </div>

            {{-- Description --}}
            @if($campaign->description)
                <div class="card p-4 sm:p-5">
                    <h2 class="font-semibold text-sm mb-3">📋 About This Giveaway</h2>
                    <div class="text-sm text-surface-600 leading-relaxed">{!! nl2br(e($campaign->description)) !!}</div>
                </div>
            @endif

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
                                    <th class="text-center">Entries</th>
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
                                        <span class="font-bold text-bonus-600">{{ $entry->entry_count }}</span>
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
                    <p class="text-sm text-surface-500 mt-1">{{ $myEntry->entry_count }} entries</p>
                @elseif($campaign->status === 'active')
                    <div class="text-4xl mb-2">📝</div>
                    <h3 class="text-lg font-bold mb-3">Join Giveaway</h3>
                    <button class="btn btn-primary w-full py-3 text-base" @click="enter()" :disabled="entering">
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
function giveawayDetail() {
    return {
        entering: false, enterError: '', enterSuccess: '',
        async enter() {
            this.entering = true; this.enterError = ''; this.enterSuccess = '';
            try {
                const res = await fetch('/customer/giveaways/{{ $campaign->id }}/enter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
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
