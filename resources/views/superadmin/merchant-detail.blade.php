@extends('layouts.app')
@section('title', $merchant->company_name . ' - BonusHub')
@section('content')
<style>
.doc-img{width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:1px solid #e5e7eb;cursor:pointer;transition:transform .2s}
.doc-img:hover{transform:scale(1.02);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
.doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:480px){.doc-grid{grid-template-columns:1fr}}
.lightbox{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.9);align-items:center;justify-content:center;padding:20px}
.lightbox.active{display:flex}
.lightbox img{max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px}
.lightbox-close{position:absolute;top:16px;right:16px;color:white;font-size:28px;cursor:pointer;background:rgba(0,0,0,0.5);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center}
</style>

<div class="page-container" style="padding-top:0">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-surface-500 mb-4 flex-wrap">
        <a href="{{ route('superadmin.merchants') }}" class="hover:text-bonus-600">Merchants</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-surface-800 font-medium">{{ $merchant->company_name }}</span>
    </div>

    {{-- Header --}}
    <div class="page-header flex-col sm:flex-row gap-3">
        <div>
            <h1 class="page-title">{{ $merchant->company_name }}</h1>
            <p class="page-subtitle flex items-center gap-2 flex-wrap">
                @if($merchant->status === 'active')<span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-medium">Active</span>
                @elseif($merchant->status === 'pending_approval')<span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full font-medium">Pending Approval</span>
                @elseif($merchant->status === 'pending_verification')<span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full font-medium">Pending Verification</span>
                @elseif($merchant->status === 'rejected')<span class="text-xs bg-red-50 text-red-700 px-2 py-0.5 rounded-full font-medium">Rejected</span>
                @else<span class="text-xs bg-surface-100 text-surface-600 px-2 py-0.5 rounded-full font-medium">{{ ucfirst($merchant->status) }}</span>@endif
                Joined {{ $merchant->created_at->format('d M Y') }} &middot; {{ $merchant->customers_count ?? 0 }} customers
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if(in_array($merchant->status, ['pending_approval', 'pending_verification']))
                <button onclick="approveMerchant({{ $merchant->id }})" class="btn-primary bg-green-600 hover:bg-green-700 text-sm px-4 py-2">✅ Approve</button>
                <button onclick="rejectMerchant({{ $merchant->id }})" class="btn-danger text-sm px-4 py-2">❌ Reject</button>
            @endif
            <button onclick="openEditModal({{ $merchant->id }})" class="btn-secondary text-sm px-4 py-2">✏️ Edit</button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="card p-4 border-l-4 border-bonus-500"><p class="text-xs text-surface-500">Customers</p><p class="text-2xl font-bold mt-1">{{ number_format($merchant->customers_count ?? 0) }}</p></div>
        <div class="card p-4 border-l-4 border-emerald-500"><p class="text-xs text-surface-500">Transactions</p><p class="text-2xl font-bold mt-1">{{ number_format($merchant->points_transactions_count ?? 0) }}</p></div>
        <div class="card p-4 border-l-4 border-amber-500"><p class="text-xs text-surface-500">Rewards</p><p class="text-2xl font-bold mt-1">{{ number_format($merchant->rewards_count ?? 0) }}</p></div>
        <div class="card p-4 border-l-4 border-purple-500"><p class="text-xs text-surface-500">Branches</p><p class="text-2xl font-bold mt-1">{{ count($merchant->branches ?? []) }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Sidebar --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Verification Documents --}}
            @if($merchant->ic_image || $merchant->ic_image_back || $merchant->ssm_image)
            <div class="card overflow-hidden">
                <div class="px-4 pt-4 pb-2 border-b border-surface-100">
                    <h2 class="font-semibold text-sm flex items-center gap-2">
                        📄 Verification Documents
                        @if($merchant->consent_pdpa)<span class="text-xs bg-green-50 text-green-700 px-1.5 py-0.5 rounded">PDPA ✓</span>@endif
                    </h2>
                </div>
                <div class="p-4 space-y-4">
                    {{-- IC Images --}}
                    @if($merchant->ic_image || $merchant->ic_image_back)
                    <div>
                        <p class="text-xs font-medium text-surface-500 mb-2 uppercase tracking-wider">MyKad (IC)</p>
                        <div class="doc-grid">
                            @if($merchant->ic_image)
                            <div>
                                <p class="text-xs text-surface-400 mb-1">Depan</p>
                                <img src="{{ asset('storage/' . $merchant->ic_image) }}" alt="IC Depan" class="doc-img" onclick="openLightbox(this.src)">
                            </div>
                            @endif
                            @if($merchant->ic_image_back)
                            <div>
                                <p class="text-xs text-surface-400 mb-1">Belakang</p>
                                <img src="{{ asset('storage/' . $merchant->ic_image_back) }}" alt="IC Belakang" class="doc-img" onclick="openLightbox(this.src)">
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- SSM Image --}}
                    @if($merchant->ssm_image)
                    <div>
                        <p class="text-xs font-medium text-surface-500 mb-2 uppercase tracking-wider">SSM / Borang 9</p>
                        <img src="{{ asset('storage/' . $merchant->ssm_image) }}" alt="SSM" class="doc-img" onclick="openLightbox(this.src)">
                    </div>
                    @endif

                    {{-- Approve/Reject for pending --}}
                    @if(in_array($merchant->status, ['pending_approval', 'pending_verification']))
                    <div class="flex gap-2 pt-2 border-t border-surface-100">
                        <button onclick="approveMerchant({{ $merchant->id }})" class="flex-1 btn-primary bg-green-600 hover:bg-green-700 text-sm py-2">✅ Approve</button>
                        <button onclick="rejectMerchant({{ $merchant->id }})" class="flex-1 btn-danger text-sm py-2">❌ Reject</button>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="card p-5 text-center text-surface-400">
                <div class="text-3xl mb-2">📄</div>
                <p class="text-sm">No documents uploaded yet</p>
            </div>
            @endif

            {{-- Merchant Info --}}
            <div class="card p-4">
                <h2 class="font-semibold text-sm mb-3">Merchant Info</h2>
                <div class="space-y-3 text-sm">
                    <div><span class="text-surface-500 block text-xs uppercase">Owner</span><span class="font-medium">{{ $merchant->owner_name ?? '-' }}</span></div>
                    <div><span class="text-surface-500 block text-xs uppercase">Phone</span><span>{{ $merchant->phone ?? '-' }}</span></div>
                    <div><span class="text-surface-500 block text-xs uppercase">Address</span><span>{{ $merchant->address ?? '-' }}</span></div>
                    <div><span class="text-surface-500 block text-xs uppercase">Package</span><span>{{ $merchant->package->name ?? '-' }} <span class="text-surface-400">(RM{{ number_format($merchant->package->price ?? 0, 0) }}/mo)</span></span></div>
                    <div><span class="text-surface-500 block text-xs uppercase">Expiry</span><span>{{ $merchant->subscription_expiry ? $merchant->subscription_expiry->format('d M Y') : '-' }}</span></div>
                </div>
            </div>

            {{-- Branches --}}
            <div class="card p-4">
                <h2 class="font-semibold text-sm mb-3">Branches ({{ count($merchant->branches ?? []) }})</h2>
                @forelse($merchant->branches as $branch)
                <div class="p-3 rounded-lg bg-surface-50 border border-surface-100 mb-2 last:mb-0">
                    <p class="font-medium text-sm">{{ $branch->name }}</p>
                    <p class="text-xs text-surface-500">{{ $branch->address }}</p>
                    @if($branch->phone)<p class="text-xs text-surface-400">{{ $branch->phone }}</p>@endif
                </div>
                @empty
                <p class="text-sm text-surface-400">No branches registered.</p>
                @endforelse
            </div>
        </div>

        {{-- Right Content --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Transactions --}}
            <div class="card overflow-hidden">
                <div class="px-4 pt-4 pb-2"><h2 class="font-semibold text-sm">Recent Transactions</h2></div>
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead><tr><th>Date</th><th>Type</th><th>Points</th><th>Amount</th><th>Status</th><th>Customer</th></tr></thead>
                        <tbody>
                        @forelse($recentTransactions as $txn)
                        <tr>
                            <td class="text-xs text-surface-500">{{ $txn->created_at->format('d M H:i') }}</td>
                            <td>@if($txn->type=='earn')<span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Earn</span>@elseif($txn->type=='redeem')<span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">Redeem</span>@else<span class="text-xs bg-surface-100 text-surface-600 px-2 py-0.5 rounded-full">{{ ucfirst($txn->type) }}</span>@endif</td>
                            <td class="font-medium">{{ number_format($txn->points, 0) }}</td>
                            <td class="text-surface-500">RM{{ number_format($txn->amount_spent ?? 0, 2) }}</td>
                            <td>@if($txn->status=='approved')<span class="text-xs text-green-600 font-medium">Approved</span>@elseif($txn->status=='pending_approval')<span class="text-xs text-amber-600 font-medium">Pending</span>@else<span class="text-xs text-surface-500">{{ ucfirst($txn->status) }}</span>@endif</td>
                            <td class="text-surface-500">#{{ $txn->customer_id }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-surface-400 py-8">No transactions yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Rewards --}}
            <div class="card overflow-hidden">
                <div class="px-4 pt-4 pb-2"><h2 class="font-semibold text-sm">Rewards ({{ $merchant->rewards_count ?? 0 }})</h2></div>
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead><tr><th>Name</th><th>Points</th><th>Stock</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($rewards as $reward)
                        <tr>
                            <td class="font-medium">{{ $reward->name }}</td>
                            <td>{{ number_format($reward->points_required, 0) }} pts</td>
                            <td class="text-surface-500">{{ $reward->stock_left }}/{{ $reward->stock_quantity }}</td>
                            <td>@if($reward->status=='active')<span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Active</span>@else<span class="text-xs bg-red-50 text-red-700 px-2 py-0.5 rounded-full">Inactive</span>@endif</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-surface-400 py-8">No rewards created.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" src="" alt="Document">
</div>

{{-- Edit Modal --}}
<div id="merchant-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 p-4" onclick="if(event.target===this)closeModal()">
    <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl">
        <div class="flex items-center justify-between p-4 border-b border-surface-100">
            <h2 class="text-lg font-bold" id="modal-title">Edit Merchant</h2>
            <button onclick="closeModal()" class="text-surface-400 hover:text-surface-600 text-2xl">&times;</button>
        </div>
        <form id="merchant-form" onsubmit="return saveMerchant(event)">
            <div class="p-4 space-y-4">
                <input type="hidden" id="edit-id">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="form-label">Merchant Name</label><input id="f-merchant_name" required class="form-input w-full"></div>
                    <div><label class="form-label">Owner Name</label><input id="f-owner_name" class="form-input w-full"></div>
                    <div><label class="form-label">Email</label><input id="f-email" type="email" required class="form-input w-full"></div>
                    <div><label class="form-label">Phone</label><input id="f-phone" class="form-input w-full"></div>
                    <div><label class="form-label">Password <span class="text-xs text-surface-400">(leave blank to keep)</span></label><input id="f-password" type="password" class="form-input w-full"></div>
                    <div><label class="form-label">Package</label>
                        <select id="f-package_id" class="form-input w-full">@foreach($packages as $p)<option value="{{ $p->id }}">{{ $p->name }} (RM{{ number_format($p->price,0) }})</option>@endforeach</select>
                    </div>
                </div>
                <div><label class="form-label">Address</label><textarea id="f-address" class="form-input w-full" rows="2"></textarea></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="form-label">Status</label>
                        <select id="f-status" class="form-input w-full">
                            <option value="active">Active</option>
                            <option value="pending_verification">Pending Verification</option>
                            <option value="pending_approval">Pending Approval</option>
                            <option value="suspended">Suspended</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 justify-end p-4 border-t border-surface-100">
                <button type="button" onclick="closeModal()" class="btn-secondary text-sm px-4 py-2">Cancel</button>
                <button type="submit" class="btn-primary text-sm px-4 py-2">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
// Lightbox
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('active');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.getElementById('lightbox-img').src = '';
}
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeLightbox(); });

// Edit Modal
function openEditModal(id) {
    document.getElementById('modal-title').textContent = 'Edit Merchant';
    document.getElementById('edit-id').value = id;
    document.getElementById('f-password').required = false;
    fetch('/superadmin/api/merchants/' + id).then(r => r.json()).then(d => {
        if (d.success) {
            let m = d.merchant;
            document.getElementById('f-merchant_name').value = m.company_name || '';
            document.getElementById('f-owner_name').value = m.owner_name || '';
            document.getElementById('f-email').value = m.users?.[0]?.email || '';
            document.getElementById('f-phone').value = m.phone || '';
            document.getElementById('f-address').value = m.address || '';
            if (m.package_id) document.getElementById('f-package_id').value = m.package_id;
            if (m.status) document.getElementById('f-status').value = m.status;
            document.getElementById('merchant-modal').classList.remove('hidden');
        }
    });
}
function closeModal() { document.getElementById('merchant-modal').classList.add('hidden'); }

function saveMerchant(e) {
    e.preventDefault();
    let id = document.getElementById('edit-id').value;
    let data = {
        merchant_name: document.getElementById('f-merchant_name').value,
        owner_name: document.getElementById('f-owner_name').value,
        name: document.getElementById('f-owner_name').value,
        email: document.getElementById('f-email').value,
        phone: document.getElementById('f-phone').value,
        package_id: document.getElementById('f-package_id').value,
        address: document.getElementById('f-address').value,
        status: document.getElementById('f-status').value,
    };
    let pwd = document.getElementById('f-password').value;
    if (pwd) data.password = pwd;

    fetch(id ? '/superadmin/api/merchants/' + id : '/superadmin/api/merchants', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data)
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message || 'Error');
    });
    return false;
}

// Approve/Reject
function approveMerchant(id) {
    if (!confirm('Approve this merchant?')) return;
    fetch('/superadmin/api/merchants/' + id + '/approve', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message || 'Error');
    });
}
function rejectMerchant(id) {
    let reason = prompt('Reason for rejection (optional):');
    fetch('/superadmin/api/merchants/' + id + '/reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ reason: reason || '' })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message || 'Error');
    });
}
</script>
@endsection
