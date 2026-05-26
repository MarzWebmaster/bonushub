@extends('layouts.app')
@section('title', 'Merchants - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Merchant Management</h1>
            <p class="page-subtitle">{{ $merchants->total() }} merchants registered</p>
        </div>
        <button onclick="openCreateModal()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Merchant
        </button>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Customers</th>
                    <th>Package</th>
                    <th>Joined</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merchants as $m)
                <tr>
                    <td class="font-medium">{{ $m->company_name }}</td>
                    <td class="text-surface-500">{{ $m->phone ?? '—' }}</td>
                    <td>
                        @if($m->is_active)
                            <span class="badge-success">Active</span>
                        @else
                            <span class="badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ number_format($m->customers_count ?? 0) }}</td>
                    <td class="text-surface-500">{{ $m->package->name ?? '—' }}</td>
                    <td class="text-surface-400 text-xs">{{ $m->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <button onclick="toggleStatus({{ $m->id }})" class="btn-sm {{ $m->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $m->is_active ? 'Deactivate' : 'Activate' }}">
                                {{ $m->is_active ? 'Deact' : 'Act' }}
                            </button>
                            <button onclick="openEditModal({{ $m->id }})" class="btn-sm btn-secondary">Edit</button>
                            <button onclick="deleteMerchant({{ $m->id }})" class="btn-sm btn-danger">Del</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <h3 class="empty-state-title">No Merchants Yet</h3>
                            <p class="empty-state-text">Start by adding your first merchant to the platform.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($merchants->hasPages())
        <div class="mt-4">{{ $merchants->links() }}</div>
    @endif
</div>

{{-- Modal --}}
<div id="merchant-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="text-lg font-bold" id="modal-title">Add Merchant</h2>
            <button onclick="closeModal()" class="text-surface-400 hover:text-surface-600">&times;</button>
        </div>
        <form id="merchant-form" onsubmit="return saveMerchant(event)">
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Merchant Name</label><input id="f-merchant_name" required class="form-input"></div>
                    <div><label class="form-label">Admin Name</label><input id="f-name" required class="form-input"></div>
                    <div><label class="form-label">Email</label><input id="f-email" type="email" required class="form-input"></div>
                    <div><label class="form-label">Phone</label><input id="f-phone" class="form-input"></div>
                    <div><label class="form-label">Password</label><input id="f-password" type="password" class="form-input"></div>
                    <div><label class="form-label">Package</label>
                        <select id="f-package_id" class="form-select">
                            @foreach($packages as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (RM{{ number_format($p->price,0) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4"><label class="form-label">Address</label><textarea id="f-address" class="form-textarea" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Merchant</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal(){document.getElementById('modal-title').textContent='Add Merchant';document.getElementById('edit-id').value='';document.getElementById('f-password').required=true;document.getElementById('merchant-modal').classList.remove('hidden');}
function closeModal(){document.getElementById('merchant-modal').classList.add('hidden');}
function openEditModal(id){document.getElementById('modal-title').textContent='Edit Merchant';document.getElementById('edit-id').value=id;document.getElementById('f-password').required=false;fetch('/superadmin/api/merchants/'+id).then(r=>r.json()).then(d=>{if(d.success){let m=d.merchant;document.getElementById('f-merchant_name').value=m.name;document.getElementById('f-name').value=m.admins?.[0]?.name||'';document.getElementById('f-email').value=m.admins?.[0]?.email||'';document.getElementById('f-phone').value=m.phone||'';if(m.package_id)document.getElementById('f-package_id').value=m.package_id;document.getElementById('f-address').value=m.address||'';document.getElementById('merchant-modal').classList.remove('hidden');}});}
function saveMerchant(e){e.preventDefault();let id=document.getElementById('edit-id').value;let url=id?'/superadmin/api/merchants/'+id:'/superadmin/api/merchants';let method=id?'PUT':'POST';let data={merchant_name:document.getElementById('f-merchant_name').value,name:document.getElementById('f-name').value,email:document.getElementById('f-email').value,phone:document.getElementById('f-phone').value,package_id:document.getElementById('f-package_id').value,address:document.getElementById('f-address').value};let pwd=document.getElementById('f-password').value;if(pwd)data.password=pwd;fetch(url,{method:method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(data)}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}else{alert(d.message||'Error');}});return false;}
function toggleStatus(id){fetch('/superadmin/api/merchants/'+id+'/toggle',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();});}
function deleteMerchant(id){if(confirm('Delete this merchant?')){fetch('/superadmin/api/merchants/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();});}}
</script>
@endsection