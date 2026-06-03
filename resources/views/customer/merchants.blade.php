@extends('layouts.app')
@section('title', 'Join Merchants')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">🏪 Join Merchants</h1>
            <p class="text-surface-500 text-sm mt-1">Discover and join loyalty programs</p>
        </div>
    </div>

    <div id="flash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($merchants as $m)
        <div class="card p-5 relative {{ $m->joined ? 'border-emerald-300 bg-emerald-50/30' : '' }}">
            @if($m->joined)
                <span class="absolute top-3 right-3 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">✓ Joined</span>
            @endif

            <h3 class="font-semibold text-surface-800 text-lg mb-1">{{ $m->company_name }}</h3>
            <p class="text-xs text-surface-400 mb-3">{{ $m->branch_count }} cawangan</p>

            {{-- Promos --}}
            @if($m->promo_list->isNotEmpty())
            <div class="mb-3 space-y-1">
                @foreach($m->promo_list as $promo)
                <div class="text-xs px-2 py-1 rounded
                    @if($promo['type'] === 'registration_bonus') bg-blue-50 text-blue-700
                    @elseif($promo['type'] === 'multiplier') bg-purple-50 text-purple-700
                    @else bg-amber-50 text-amber-700 @endif">
                    🎉 {{ $promo['name'] }}
                    @if($promo['type'] === 'multiplier')
                        ({{ $promo['value'] }}x)
                    @else
                        (+{{ number_format($promo['value']) }} pts)
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Rewards count --}}
            <p class="text-sm text-surface-500 mb-4">
                🎁 {{ $m->reward_count }} items to redeem
            </p>

            <button
                onclick="joinMerchant({{ $m->id }}, '{{ addslashes($m->company_name) }}', this)"
                class="w-full py-2 rounded-lg text-sm font-medium transition
                {{ $m->joined
                    ? 'bg-emerald-100 text-emerald-600 cursor-not-allowed'
                    : 'bg-bonus-600 text-white hover:bg-bonus-700' }}"
                {{ $m->joined ? 'disabled' : '' }}>
                {{ $m->joined ? '✓ Joined' : 'Join Now' }}
            </button>
        </div>
        @endforeach
    </div>

    @if($merchants->isEmpty())
    <div class="card text-center py-12 text-surface-400">
        <div class="text-4xl mb-3">🏪</div>
        <p>No merchants available yet.</p>
    </div>
    @endif
</div>

<script>
async function joinMerchant(id, name, btn) {
    btn.disabled = true;
    btn.textContent = 'Joining...';

    try {
        const res = await fetch('/customer/merchants/' + id + '/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            }
        });
        const d = await res.json();

        if (d.success) {
            flash(d.message, 'success');
            btn.textContent = '✓ Joined';
            btn.className = 'w-full py-2 rounded-lg text-sm font-medium transition bg-emerald-100 text-emerald-600 cursor-not-allowed';

            // Update parent card
            const card = btn.closest('.card');
            card.classList.add('border-emerald-300', 'bg-emerald-50/30');
            const badge = document.createElement('span');
            badge.className = 'absolute top-3 right-3 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium';
            badge.textContent = '✓ Joined';
            card.prepend(badge);
        } else {
            flash(d.message || 'Failed to join.', 'error');
            btn.disabled = false;
            btn.textContent = 'Join Now';
        }
    } catch(err) {
        flash('Network error.', 'error');
        btn.disabled = false;
        btn.textContent = 'Join Now';
    }
}

function flash(msg, type) {
    const el = document.getElementById('flash');
    el.textContent = msg;
    el.className = 'mb-4 px-4 py-3 rounded-lg text-sm ' + (type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700');
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}
</script>
@endsection
