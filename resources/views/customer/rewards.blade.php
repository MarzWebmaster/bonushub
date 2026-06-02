@extends('layouts.app')
@section('title','Rewards - Customer')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Available Rewards</h1>
            <p class="page-subtitle">Redeem your points for rewards</p>
        </div>
    </div>

    {{-- Toast notification --}}
    <div id="toast" class="hidden fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="rewards-grid">
        <div class="col-span-3 text-center text-surface-400 py-8">Loading rewards...</div>
    </div>
</div>
<script>
const CSRF = '{{ csrf_token() }}';

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm ' +
        (type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white');
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3000);
}

function redeem(rewardId, merchantId, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin inline-block">⏳</span> Redeeming...';

    fetch('{{ route("customer.redeem") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reward_product_id: rewardId,
            merchant_id: merchantId,
            quantity: 1
        })
    })
    .then(r => r.json().then(j => ({status: r.status, body: j})))
    .then(({status, body}) => {
        if (body.success) {
            showToast(body.message || 'Redeemed!', 'success');
            btn.innerHTML = '✅ Done';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
        } else {
            showToast(body.message || 'Redemption failed', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Redeem';
        }
    })
    .catch(() => {
        showToast('Network error. Try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Redeem';
    });
}

fetch('/customer/api/rewards').then(r => r.json()).then(d => {
    const grid = document.getElementById('rewards-grid');
    if (d.rewards && d.rewards.length) {
        grid.innerHTML = d.rewards.map(r =>
            '<div class="card p-4">'
            + '<h3 class="font-bold text-surface-800 dark:text-white">' + r.name + '</h3>'
            + '<p class="text-sm text-surface-500">' + (r.merchant ? r.merchant.company_name : '-') + '</p>'
            + '<p class="text-2xl font-bold text-bonus-600 mt-2">' + r.points_required + ' pts</p>'
            + (r.stock_left !== null ? '<p class="text-xs text-surface-400 mt-1">Stock: ' + r.stock_left + '</p>' : '')
            + '<button onclick="redeem(' + r.id + ',' + r.merchant_id + ',this)" class="btn-primary w-full text-sm mt-3">Redeem</button>'
            + '</div>'
        ).join('');
    } else {
        grid.innerHTML = '<div class="col-span-3 text-center py-12"><p class="text-surface-400">No rewards available</p></div>';
    }
}).catch(() => {
    document.getElementById('rewards-grid').innerHTML = '<div class="col-span-3 text-center py-12"><p class="text-red-400">Failed to load rewards</p></div>';
});
</script>
@endsection
