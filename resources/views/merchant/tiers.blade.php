@extends('layouts.app')
@section('title','Tier Settings - Merchant')
@section('content')
<div class="page-container">
    {{-- Header: stack on mobile, row on desktop --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-surface-800">Tier Settings</h1>
            <p class="text-xs sm:text-sm text-surface-500 mt-0.5">Set point thresholds. Customers auto-assigned based on their points.</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="save-status" class="text-xs sm:text-sm hidden"></span>
            <button onclick="saveTiers()" id="save-btn" class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 active:scale-95 transition-all shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Save
            </button>
        </div>
    </div>

    {{-- Tier Table --}}
    <div class="card overflow-hidden mb-4">
        <div class="divide-y divide-surface-100">
            {{-- Basic --}}
            <div class="flex items-center gap-3 p-3 sm:p-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-surface-100 flex items-center justify-center text-base sm:text-lg shrink-0">🥉</div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-surface-800 text-sm sm:text-base">Basic</h3>
                    <p class="text-xs text-surface-400 hidden sm:block">Starting tier for all customers</p>
                </div>
                <div class="text-right shrink-0">
                    <input type="number" id="tier-basic" value="0" min="0" class="w-20 sm:w-28 px-2 py-1.5 rounded-lg border border-surface-200 bg-surface-50 text-surface-500 text-sm text-right" disabled>
                    <p class="text-[10px] text-surface-400 mt-0.5">Fixed</p>
                </div>
            </div>

            {{-- Silver --}}
            <div class="flex items-center gap-3 p-3 sm:p-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-100 flex items-center justify-center text-base sm:text-lg shrink-0">🥈</div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-surface-800 text-sm sm:text-base">Silver</h3>
                    <p class="text-xs text-surface-400 hidden sm:block">Customers who earned enough points</p>
                </div>
                <div class="text-right shrink-0">
                    <input type="number" id="tier-silver" value="500" min="0" class="w-20 sm:w-28 px-2 py-1.5 rounded-lg border border-surface-200 focus:border-bonus-500 focus:ring-1 focus:ring-bonus-500 text-surface-700 text-sm text-right">
                    <p class="text-[10px] text-surface-400 mt-0.5">pts min</p>
                </div>
            </div>

            {{-- Gold --}}
            <div class="flex items-center gap-3 p-3 sm:p-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-yellow-50 flex items-center justify-center text-base sm:text-lg shrink-0">🥇</div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-surface-800 text-sm sm:text-base">Gold</h3>
                    <p class="text-xs text-surface-400 hidden sm:block">Loyal high-spending customers</p>
                </div>
                <div class="text-right shrink-0">
                    <input type="number" id="tier-gold" value="2000" min="0" class="w-20 sm:w-28 px-2 py-1.5 rounded-lg border border-surface-200 focus:border-bonus-500 focus:ring-1 focus:ring-bonus-500 text-surface-700 text-sm text-right">
                    <p class="text-[10px] text-surface-400 mt-0.5">pts min</p>
                </div>
            </div>

            {{-- Platinum --}}
            <div class="flex items-center gap-3 p-3 sm:p-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-purple-50 flex items-center justify-center text-base sm:text-lg shrink-0">💎</div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-surface-800 text-sm sm:text-base">Platinum</h3>
                    <p class="text-xs text-surface-400 hidden sm:block">Top-tier VIP customers</p>
                </div>
                <div class="text-right shrink-0">
                    <input type="number" id="tier-platinum" value="5000" min="0" class="w-20 sm:w-28 px-2 py-1.5 rounded-lg border border-surface-200 focus:border-bonus-500 focus:ring-1 focus:ring-bonus-500 text-surface-700 text-sm text-right">
                    <p class="text-[10px] text-surface-400 mt-0.5">pts min</p>
                </div>
            </div>
        </div>
    </div>

    {{-- How It Works --}}
    <div class="card p-3 sm:p-5">
        <h3 class="font-bold text-surface-800 mb-2 text-sm sm:text-base">How It Works</h3>
        <ul class="text-xs sm:text-sm text-surface-600 space-y-1">
            <li>• Customers <strong>auto-assigned</strong> tier based on total points</li>
            <li>• Save recalculates <strong>all customers</strong> instantly</li>
            <li>• Points are cumulative — earned points count toward upgrades</li>
        </ul>
    </div>
</div>

<script>
fetch('/merchant/api/tiers').then(r => r.json()).then(d => {
    if (d.success) {
        d.tiers.forEach(t => {
            const el = document.getElementById('tier-' + t.tier_name.toLowerCase());
            if (el) el.value = t.min_points;
        });
    }
});

function saveTiers() {
    const btn = document.getElementById('save-btn');
    const status = document.getElementById('save-status');
    btn.disabled = true;
    const orig = btn.textContent;
    btn.textContent = '...';
    status.className = 'text-xs sm:text-sm hidden';
    status.textContent = '';

    const tiers = [
        { tier_name: 'Basic', min_points: 0 },
        { tier_name: 'Silver', min_points: parseInt(document.getElementById('tier-silver').value) || 0 },
        { tier_name: 'Gold', min_points: parseInt(document.getElementById('tier-gold').value) || 0 },
        { tier_name: 'Platinum', min_points: parseInt(document.getElementById('tier-platinum').value) || 0 },
    ];

    fetch('/merchant/api/tiers', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ tiers })
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.textContent = orig;
        status.className = d.success ? 'text-xs sm:text-sm text-emerald-600' : 'text-xs sm:text-sm text-red-500';
        status.textContent = d.success ? '✓ Saved!' : (d.message || 'Error');
        status.classList.remove('hidden');
        setTimeout(() => status.classList.add('hidden'), 3000);
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = orig;
        status.className = 'text-xs sm:text-sm text-red-500';
        status.textContent = 'Error';
        status.classList.remove('hidden');
    });
}
</script>
@endsection
