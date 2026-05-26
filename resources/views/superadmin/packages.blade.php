@extends('layouts.app')
@section('title', 'Packages - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Package Management</h1>
            <p class="page-subtitle">{{ $packages->total() }} subscription plans</p>
        </div>
        <button onclick="openCreateModal()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Package
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($packages as $p)
        <div class="card overflow-hidden">
            <div class="p-6 {{ $p->price == 0 ? 'bg-gradient-to-br from-surface-100 to-surface-200' : ($p->price == 49 ? 'bg-gradient-to-br from-bonus-50 to-blue-50' : ($p->price == 129 ? 'bg-gradient-to-br from-amber-50 to-orange-50' : 'bg-gradient-to-br from-purple-50 to-pink-50')) }}">
                <h3 class="text-lg font-bold {{ $p->price == 0 ? 'text-surface-700' : 'text-bonus-700' }}">{{ $p->name }}</h3>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-3xl font-bold">RM{{ number_format($p->price, 0) }}</span>
                    <span class="text-sm text-surface-500">/mo</span>
                </div>
            </div>
            <div class="p-6 border-t border-surface-100">
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm">
                        <svg class="w-4 h-4 mt-0.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>{{ $p->branch_limit > 0 ? $p->branch_limit : 'Unlimited' }} Branches</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm">
                        <svg class="w-4 h-4 mt-0.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>{{ $p->staff_limit > 0 ? $p->staff_limit : 'Unlimited' }} Staff</span>
                    </li>
                    @if($p->features)
                        @foreach(json_decode($p->features, true) ?? [] as $feat)
                        <li class="flex items-start gap-3 text-sm text-surface-600">
                            <svg class="w-4 h-4 mt-0.5 text-bonus-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>{{ $feat }}</span>
                        </li>
                        @endforeach
                    @endif
                </ul>
                <div class="mt-6 flex gap-2">
                    <button onclick="openEditModal({{ $p->id }}, '{{ $p->name }}', {{ $p->price }}, {{ $p->branch_limit }}, {{ $p->staff_limit }}, '{{ addslashes($p->features ?? '') }}')" class="btn-sm btn-secondary flex-1">Edit</button>
                    <button onclick="deletePackage({{ $p->id }})" class="btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal --}}
<div id="package-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="text-lg font-bold" id="modal-title">Add Package</h2>
            <button onclick="closeModal()" class="text-surface-400 hover:text-surface-600">&times;</button>
        </div>
        <form id="package-form" onsubmit="return savePackage(event)">
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Name</label><input id="f-name" required class="form-input" placeholder="e.g. Starter"></div>
                    <div><label class="form-label">Price (RM/mo)</label><input id="f-price" type="number" step="0.01" required class="form-input" placeholder="0.00"></div>
                    <div><label class="form-label">Branch Limit</label><input id="f-branch" type="number" value="1" class="form-input" placeholder="-1 for unlimited"></div>
                    <div><label class="form-label">Staff Limit</label><input id="f-staff" type="number" value="2" class="form-input" placeholder="-1 for unlimited"></div>
                </div>
                <div class="mt-4">
                    <label class="form-label">Features (one per line)</label>
                    <textarea id="f-features" class="form-textarea" rows="4" placeholder="Loyalty System&#10;Giveaway Campaigns&#10;API Access"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal(){document.getElementById('modal-title').textContent='Add Package';document.getElementById('edit-id').value='';document.getElementById('f-name').value='';document.getElementById('f-price').value='';document.getElementById('f-branch').value=1;document.getElementById('f-staff').value=2;document.getElementById('f-features').value='';document.getElementById('package-modal').classList.remove('hidden');}
function closeModal(){document.getElementById('package-modal').classList.add('hidden');}
function openEditModal(id,name,price,branch,staff,features){document.getElementById('modal-title').textContent='Edit Package';document.getElementById('edit-id').value=id;document.getElementById('f-name').value=name;document.getElementById('f-price').value=price;document.getElementById('f-branch').value=branch;document.getElementById('f-staff').value=staff;try{document.getElementById('f-features').value=JSON.parse(features).join('\n');}catch(e){document.getElementById('f-features').value='';}document.getElementById('package-modal').classList.remove('hidden');}
function savePackage(e){e.preventDefault();let id=document.getElementById('edit-id').value;let url=id?'/superadmin/api/packages/'+id:'/superadmin/api/packages';let method=id?'PUT':'POST';let features=document.getElementById('f-features').value.split('\n').filter(f=>f.trim());let data={name:document.getElementById('f-name').value,price:document.getElementById('f-price').value,branch_limit:parseInt(document.getElementById('f-branch').value)||1,staff_limit:parseInt(document.getElementById('f-staff').value)||2,features:features};fetch(url,{method:method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(data)}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}else{alert(d.message||'Error');}});return false;}
function deletePackage(id){if(confirm('Delete this package?')){fetch('/superadmin/api/packages/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();});}}
</script>
@endsection