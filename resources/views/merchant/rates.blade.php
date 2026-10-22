@extends('layouts.app')
@section('title', 'Loyalty Rates - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Loyalty Rates</h1>
            <p class="page-subtitle">Configure earning and redemption rates</p>
        </div>
    </div>
    <div class="card p-4 sm:p-6">
        <div id="rates-success" class="hidden mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">Saved!</div>
        <form id="rates-frm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="form-label">Earn Rate (pts per RM)</label>
                <input name="earn_rate" type="number" step="0.01" min="0.01" class="form-input" value="{{ $rate->earn_rate ?? '1.00' }}" required></div>
                <div><label class="form-label">Redeem Rate (pts per RM)</label>
                <input name="redeem_rate" type="number" step="0.01" min="1" class="form-input" value="{{ $rate->redeem_rate ?? '1.00' }}" required></div>
                <div><label class="form-label">Min Redeem Points</label>
                <input name="min_redeem" type="number" min="0" class="form-input" value="{{ $rate->min_redeem ?? '' }}"></div>
                <div><label class="form-label">Max Redeem Points</label>
                <input name="max_redeem" type="number" min="0" class="form-input" value="{{ $rate->max_redeem ?? '' }}"></div>
            </div>
            <div class="border-t border-gray-200 pt-4">
                <h2 class="text-sm font-semibold text-gray-900">Customer Referral Bonus</h2>
                <p class="text-xs text-gray-500 mt-1">Points given when a friend a customer referred registers and becomes active (after a few days). Leave at 0 to switch referral bonuses off.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                    <div><label class="form-label">Bonus for the referrer (points)</label>
                    <input name="referral_bonus_referrer" type="number" min="0" max="100000" class="form-input" value="{{ $rate->referral_bonus_referrer ?? 0 }}"></div>
                    <div><label class="form-label">Welcome bonus for the new friend (points)</label>
                    <input name="referral_bonus_referred" type="number" min="0" max="100000" class="form-input" value="{{ $rate->referral_bonus_referred ?? 0 }}"></div>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-4">
                <h2 class="text-sm font-semibold text-gray-900">Points Expiry</h2>
                <p class="text-xs text-gray-500 mt-1">Customers' points with you lapse after this many months without any earn or redeem, and they are warned {{ (int) config('points.warn_days', 30) }} days before. Leave empty for the platform default ({{ (int) config('points.default_months', 12) }} months), enter 0 so your points never expire, or choose {{ (int) config('points.min_months', 6) }}–{{ (int) config('points.max_months', 36) }} months.</p>
                @unless(config('points.expiry_enabled'))
                    <p class="text-xs text-amber-600 mt-1">Points expiry is not switched on for the platform yet, so nothing expires today. This setting is stored for when it is.</p>
                @endunless
                <div class="mt-3 sm:w-1/2">
                    <label class="form-label">Expire after (months of no activity)</label>
                    <input name="points_expiry_months" type="number" min="0" max="{{ (int) config('points.max_months', 36) }}" class="form-input" placeholder="Platform default" value="{{ $rate->points_expiry_months ?? '' }}">
                </div>
            </div>
            <button type="button" id="btn-save-rates" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Rates
            </button>
        </form>
    </div>
</div>
<script>
document.getElementById('btn-save-rates').addEventListener('click', function() {
    const fd = new FormData(document.getElementById('rates-frm'));
    fd.append('_method', 'PUT');
    const btn = this; btn.disabled = true; btn.innerHTML = 'Saving...';
    fetch('/merchant/api/loyalty-rates', { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}, body: fd })
    .then(r => r.json()).then(d => {
        btn.disabled = false; btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Rates';
        if (d.success) { const s = document.getElementById('rates-success'); s.classList.remove('hidden'); setTimeout(() => s.classList.add('hidden'), 2500); }
        else alert((d.errors ? Object.values(d.errors).flat().join(' ') : '') || d.message || 'Failed to save');
    }).catch(() => { btn.disabled = false; alert('Network error. Please try again.'); });
});
</script>
@endsection