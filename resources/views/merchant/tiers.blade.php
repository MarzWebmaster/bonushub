@extends('layouts.app')
@section('title','Tier Settings - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Tier Settings</h1>
            <p class="page-subtitle">Set point thresholds for each customer tier. Customers are auto-assigned based on their points.</p>
        </div>
    </div>

    {{-- Top Save Button --}}
    <div class="mb-5">
        <button onclick="saveTiers()" id="save-btn-top" class="w-full px-6 py-3.5 bg-bonus-600 text-white rounded-xl font-bold text-lg hover:bg-bonus-700 active:scale-[0.98] transition-all shadow-lg shadow-bonus-600/30">
            💾 Save Tier Settings
        </button>
        <span id="save-status-top" class="text-sm text-emerald-600 mt-1 block"></span>
    </div>

    {{-- Tier Config Cards --}}
    <div class="grid gap-4 md:grid-cols-2 mb-6" id="tier-cards">
        <div class="card p-5 border-l-4 border-surface-300">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-surface-100 flex items-center justify-center text-lg">🥉</div>
                <div>
                    <h3 class="font-bold text-surface-800">Basic</h3>
                    <p class="text-xs text-surface-400">Starting tier for all customers</p>
                </div>
            </div>
            <label class="text-sm text-surface-500 mb-1 block">Minimum Points</label>
            <input type="number" id="tier-basic" value="0" min="0" class="w-full px-3 py-2 rounded-lg border border-surface-200 bg-surface-50 text-surface-700" disabled>
            <p class="text-xs text-surface-400 mt-1">Always 0 — cannot be changed</p>
        </div>

        <div class="card p-5 border-l-4 border-gray-400">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-lg">🥈</div>
                <div>
                    <h3 class="font-bold text-surface-800">Silver</h3>
                    <p class="text-xs text-surface-400">Customers who earned enough points</p>
                </div>
            </div>
            <label class="text-sm text-surface-500 mb-1 block">Minimum Points</label>
            <input type="number" id="tier-silver" value="500" min="0" class="w-full px-3 py-2 rounded-lg border border-surface-200 focus:border-bonus-500 focus:ring-1 focus:ring-bonus-500 text-surface-700">
        </div>

        <div class="card p-5 border-l-4 border-yellow-500">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-yellow-50 flex items-center justify-center text-lg">🥇</div>
                <div>
                    <h3 class="font-bold text-surface-800">Gold</h3>
                    <p class="text-xs text-surface-400">Loyal high-spending customers</p>
                </div>
            </div>
            <label class="text-sm text-surface-500 mb-1 block">Minimum Points</label>
            <input type="number" id="tier-gold" value="2000" min="0" class="w-full px-3 py-2 rounded-lg border border-surface-200 focus:border-bonus-500 focus:ring-1 focus:ring-bonus-500 text-surface-700">
        </div>

        <div class="card p-5 border-l-4 border-purple-500">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-lg">💎</div>
                <div>
                    <h3 class="font-bold text-surface-800">Platinum</h3>
                    <p class="text-xs text-surface-400">Top-tier VIP customers</p>
                </div>
            </div>
            <label class="text-sm text-surface-500 mb-1 block">Minimum Points</label>
            <input type="number" id="tier-platinum" value="5000" min="0" class="w-full px-3 py-2 rounded-lg border border-surface-200 focus:border-bonus-500 focus:ring-1 focus:ring-bonus-500 text-surface-700">
        </div>
    </div>


    {{-- Preview --}}
    <div class="card p-5 mt-6">
        <h3 class="font-bold text-surface-800 mb-3">How It Works</h3>
        <div class="text-sm text-surface-600 space-y-2">
            <p>• Customers are <strong>automatically assigned</strong> a tier based on their total points.</p>
            <p>• When you save new thresholds, <strong>all existing customers</strong> are recalculated instantly.</p>
            <p>• Points are cumulative — once earned, they count toward tier upgrades.</p>
            <p>• Example: If Gold = 2,000 pts, a customer with 2,450 pts is <span class="badge-tier gold">Gold</span></p>
        </div>
    </div>
</div>

<script>
// Load current tiers
fetch('/merchant/api/tiers').then(r => r.json()).then(d => {
    if (d.success) {
        d.tiers.forEach(t => {
            const key = 'tier-' + t.tier_name.toLowerCase();
            const el = document.getElementById(key);
            if (el) el.value = t.min_points;
        });
    }
});

function saveTiers() {
    const btn = document.getElementById('save-btn-top');
    const status = document.getElementById('save-status-top');
    const statusTop = document.getElementById('save-status-top');
    btn.disabled = true;
    btn.textContent = 'Saving...';
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
        btn.textContent = 'Save Tiers';
        if (d.success) {
            status.className = 'text-sm text-emerald-600';
            status.textContent = '✓ Tiers saved & customers recalculated!'; 
            setTimeout(() => status.textContent = '', 4000);
        } else {
            status.className = 'text-sm text-red-500';
            status.textContent = 'Error: ' + (d.message || 'Validation failed'); 
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Tiers';
        status.className = 'text-sm text-red-500';
        status.textContent = 'Network error'; 
    });
}
</script>
@endsection
