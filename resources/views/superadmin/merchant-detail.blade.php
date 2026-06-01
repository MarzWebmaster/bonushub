@extends('layouts.app')
@section('title', $merchant->company_name . ' - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="flex items-center gap-2 text-sm text-surface-500 mb-4">
        <a href="{{ route('superadmin.merchants') }}" class="hover:text-bonus-600 transition-colors">Merchants</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-surface-800 font-medium">{{ $merchant->company_name }}</span>
    </div>
    <div class="page-header">
        <div><h1 class="page-title">{{ $merchant->company_name }}</h1>
            <p class="page-subtitle">@if($merchant->status == 'active')<span class="badge-success">Active</span>@else<span class="badge-danger">Inactive</span>@endif &middot; Joined {{ $merchant->created_at->format('d M Y') }} &middot; {{ $merchant->customers_count ?? 0 }} customers</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openEditModal({{ $merchant->id }})" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit Merchant</button>
        </div>
    </div>
    <div class="stats-grid mb-6">
        <div class="stat-card border-l-bonus-500"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-surface-500">Total Customers</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ number_format($merchant->customers_count ?? 0) }}</p></div><div class="w-12 h-12 rounded-xl bg-bonus-50 flex items-center justify-center"><svg class="w-6 h-6 text-bonus-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div></div></div>
        <div class="stat-card border-l-emerald-500"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-surface-500">Points Transactions</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ number_format($merchant->points_transactions_count ?? 0) }}</p></div><div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div></div>
        <div class="stat-card border-l-amber-500"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-surface-500">Rewards Created</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ number_format($merchant->rewards_count ?? 0) }}</p></div><div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center"><svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg></div></div></div>
        <div class="stat-card border-l-purple-500"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-surface-500">Branches</p><p class="text-3xl font-bold text-surface-800 mt-1">{{ count($merchant->branches ?? []) }}</p></div><div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div></div></div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="card"><div class="card-header"><h2 class="card-title">Merchant Info</h2></div><div class="card-body space-y-4">
                <div><label class="text-xs font-medium text-surface-400 uppercase tracking-wider">Phone</label><p class="text-surface-800 mt-1">{{ $merchant->phone ?? '-' }}</p></div>
                <div><label class="text-xs font-medium text-surface-400 uppercase tracking-wider">Address</label><p class="text-surface-800 mt-1">{{ $merchant->address ?? '-' }}</p></div>
                <div><label class="text-xs font-medium text-surface-400 uppercase tracking-wider">Package</label><p class="text-surface-800 mt-1">{{ $merchant->package->name ?? '-' }} <span class="text-surface-500 text-sm">(RM{{ number_format($merchant->package->price ?? 0, 0) }}/mo)</span></p></div>
                <div><label class="text-xs font-medium text-surface-400 uppercase tracking-wider">Subscription Expiry</label><p class="text-surface-800 mt-1">{{ $merchant->subscription_expiry ? $merchant->subscription_expiry->format('d M Y') : '-' }}</p></div>
            </div></div>
            @if($merchant->loyaltyRate)
            <div class="card"><div class="card-header"><h2 class="card-title">Loyalty Rate</h2></div><div class="card-body space-y-4">
                <div><label class="text-xs font-medium text-surface-400 uppercase tracking-wider">Rate per RM</label><p class="text-surface-800 mt-1">{{ $merchant->loyaltyRate->rate_per_rm }} point(s) per RM1</p></div>
                @if($merchant->loyaltyRate->festive_multiplier > 1)<div><label class="text-xs font-medium text-surface-400 uppercase tracking-wider">Festive Multiplier</label><p class="text-surface-800 mt-1">x{{ $merchant->loyaltyRate->festive_multiplier }}</p></div>@endif
            </div></div>
            @endif
            <div class="card"><div class="card-header"><h2 class="card-title">Branches ({{ count($merchant->branches ?? []) }})</h2></div><div class="card-body space-y-3">
                @forelse($merchant->branches as $branch)
                <div class="p-3 rounded-lg bg-surface-50 border border-surface-100"><p class="font-medium text-surface-800">{{ $branch->name }}</p><p class="text-sm text-surface-500">{{ $branch->address }}</p>@if($branch->phone)<p class="text-sm text-surface-400">{{ $branch->phone }}</p>@endif</div>
                @empty <p class="text-sm text-surface-400">No branches registered.</p>
                @endforelse
            </div></div>
        </div>
        <div class="lg:col-span-2 space-y-6">
            <div class="card"><div class="card-header"><h2 class="card-title">Recent Points Transactions</h2></div>
            <div class="overflow-x-auto"><table class="data-table">
                <thead><tr><th>Date</th><th>Type</th><th>Points</th><th>Amount</th><th>Status</th><th>Customer</th></tr></thead>
                <tbody>@forelse($recentTransactions as $txn)<tr>
                    <td class="text-xs text-surface-500">{{ $txn->created_at->format('d M H:i') }}</td>
                    <td>@if($txn->type == 'earn')<span class="badge-success">Earn</span>@elseif($txn->type == 'redeem')<span class="badge-warning">Redeem</span>@else<span class="badge-info">{{ ucfirst($txn->type) }}</span>@endif</td>
                    <td class="font-medium">{{ number_format($txn->points, 0) }}</td>
                    <td class="text-surface-500">RM{{ number_format($txn->amount_spent ?? 0, 2) }}</td>
                    <td>@if($txn->status == 'approved')<span class="text-emerald-600 text-xs font-medium">Approved</span>@elseif($txn->status == 'pending_approval')<span class="text-amber-600 text-xs font-medium">Pending</span>@else<span class="text-surface-500 text-xs">{{ ucfirst($txn->status) }}</span>@endif</td>
                    <td class="text-surface-500 text-sm">#{{ $txn->customer_id }}</td>
                </tr>@empty<tr><td colspan="6" class="text-center text-surface-400 py-8">No transactions yet.</td></tr>@endforelse
                </tbody></table></div></div>
            <div class="card"><div class="card-header"><h2 class="card-title">Rewards ({{ $merchant->rewards_count ?? 0 }})</h2></div>
            <div class="overflow-x-auto"><table class="data-table">
                <thead><tr><th>Name</th><th>Points Required</th><th>Stock</th><th>Status</th></tr></thead>
                <tbody>@forelse($rewards as $reward)<tr>
                    <td class="font-medium">{{ $reward->name }}</td><td>{{ number_format($reward->points_required, 0) }} pts</td><td class="text-surface-500">{{ $reward->stock_left }}/{{ $reward->stock_quantity }}</td>
                    <td>@if($reward->status == 'active')<span class="badge-success">Active</span>@else<span class="badge-danger">Inactive</span>@endif</td>
                </tr>@empty<tr><td colspan="4" class="text-center text-surface-400 py-8">No rewards created.</td></tr>@endforelse
                </tbody></table></div></div>
        </div>
    </div>
</div>
<div id="merchant-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeModal()">
    <div class="modal-content">
        <div class="modal-header"><h2 class="text-lg font-bold" id="modal-title">Edit Merchant</h2><button onclick="closeModal()" class="text-surface-400 hover:text-surface-600">&times;</button></div>
        <form id="merchant-form" onsubmit="return saveMerchant(event)"><div class="modal-body">
            <input type="hidden" id="edit-id">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Merchant Name</label><input id="f-merchant_name" required class="form-input"></div>
                <div><label class="form-label">Admin Name</label><input id="f-name" required class="form-input"></div>
                <div><label class="form-label">Email</label><input id="f-email" type="email" required class="form-input"></div>
                <div><label class="form-label">Phone</label><input id="f-phone" class="form-input"></div>
                <div><label class="form-label">Password</label><input id="f-password" type="password" class="form-input"></div>
                <div><label class="form-label">Package</label><select id="f-package_id" class="form-select">@foreach($packages as $p)<option value="{{ $p->id }}">{{ $p->name }} (RM{{ number_format($p->price,0) }})</option>@endforeach</select></div>
            </div>
            <div class="mt-4"><label class="form-label">Address</label><textarea id="f-address" class="form-textarea" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button><button type="submit" class="btn-primary">Save Merchant</button></div></form>
    </div>
</div>
<script>
function openEditModal(id){document.getElementById('modal-title').textContent='Edit Merchant';document.getElementById('edit-id').value=id;document.getElementById('f-password').required=false;fetch('/superadmin/api/merchants/'+id).then(r=>r.json()).then(d=>{if(d.success){let m=d.merchant;document.getElementById('f-merchant_name').value=m.company_name;document.getElementById('f-name').value=m.users?.[0]?.name||'';document.getElementById('f-email').value=m.users?.[0]?.email||'';document.getElementById('f-phone').value=m.phone||'';if(m.package_id)document.getElementById('f-package_id').value=m.package_id;document.getElementById('f-address').value=m.address||'';document.getElementById('merchant-modal').classList.remove('hidden');}});}
function closeModal(){document.getElementById('merchant-modal').classList.add('hidden');}
function saveMerchant(e){e.preventDefault();let id=document.getElementById('edit-id').value;let url=id?'/superadmin/api/merchants/'+id:'/superadmin/api/merchants';let method=id?'PUT':'POST';let data={merchant_name:document.getElementById('f-merchant_name').value,name:document.getElementById('f-name').value,email:document.getElementById('f-email').value,phone:document.getElementById('f-phone').value,package_id:document.getElementById('f-package_id').value,address:document.getElementById('f-address').value};let pwd=document.getElementById('f-password').value;if(pwd)data.password=pwd;fetch(url,{method:method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(data)}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}else{alert(d.message||'Error');}});return false;}
</script>
@endsection