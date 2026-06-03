@extends('layouts.app')
@section('title', 'Profil Syarikat')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Profil Syarikat</h1>
            <p class="text-surface-500 text-sm mt-1">Urus maklumat syarikat & cawangan anda</p>
        </div>
    </div>

    {{-- Company Info Card --}}
    <div class="card mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-surface-800">Maklumat Syarikat</h2>
            <button onclick="toggleEditProfile()" class="btn-primary text-sm px-4 py-2" id="edit-profile-btn">
                ✏️ Edit
            </button>
        </div>

        {{-- Display Mode --}}
        <div id="profile-display">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-surface-400 uppercase tracking-wider">Nama Syarikat</label>
                    <p class="text-surface-800 font-medium" id="display-company">{{ $merchant->company_name }}</p>
                </div>
                <div>
                    <label class="text-xs text-surface-400 uppercase tracking-wider">Telefon</label>
                    <p class="text-surface-800 font-medium" id="display-phone">{{ $merchant->phone ?? '-' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-surface-400 uppercase tracking-wider">Alamat</label>
                    <p class="text-surface-800 font-medium" id="display-address">{{ $merchant->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Edit Mode --}}
        <div id="profile-edit" class="hidden">
            <form onsubmit="saveProfile(event)" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nama Syarikat</label>
                        <input type="text" name="company_name" id="edit-company" class="form-input" value="{{ $merchant->company_name }}" required>
                    </div>
                    <div>
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" id="edit-phone" class="form-input" value="{{ $merchant->phone }}" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit-address" class="form-input" rows="2">{{ $merchant->address }}</textarea>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary text-sm px-4 py-2">💾 Simpan</button>
                    <button type="button" onclick="toggleEditProfile()" class="btn-secondary text-sm px-4 py-2">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Branches Card --}}
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-surface-800">Cawangan ({{ $branches->count() }})</h2>
            <button onclick="showAddBranch()" class="btn-primary text-sm px-4 py-2">
                + Tambah Cawangan
            </button>
        </div>

        {{-- Add Branch Form (hidden) --}}
        <div id="add-branch-form" class="hidden mb-6 p-4 bg-surface-50 rounded-lg border border-surface-200">
            <h3 class="text-sm font-semibold text-surface-700 mb-3">Tambah Cawangan Baru</h3>
            <form onsubmit="saveBranch(event)" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Nama Cawangan</label>
                        <input type="text" name="name" id="new-branch-name" class="form-input" placeholder="Cth: Cawangan Puncak Alam" required>
                    </div>
                    <div>
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" id="new-branch-phone" class="form-input" placeholder="013XXXXXXX">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="new-branch-address" class="form-input" rows="2" placeholder="Alamat cawangan..."></textarea>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary text-sm px-4 py-2">💾 Simpan</button>
                    <button type="button" onclick="hideAddBranch()" class="btn-secondary text-sm px-4 py-2">Batal</button>
                </div>
            </form>
        </div>

        {{-- Edit Branch Form (hidden) --}}
        <div id="edit-branch-form" class="hidden mb-6 p-4 bg-surface-50 rounded-lg border border-surface-200">
            <h3 class="text-sm font-semibold text-surface-700 mb-3">Edit Cawangan</h3>
            <form onsubmit="updateBranch(event)" class="space-y-3">
                @csrf
                <input type="hidden" name="branch_id" id="edit-branch-id">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Nama Cawangan</label>
                        <input type="text" name="name" id="edit-branch-name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" id="edit-branch-phone" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" id="edit-branch-status" class="form-input">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit-branch-address" class="form-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary text-sm px-4 py-2">💾 Simpan</button>
                    <button type="button" onclick="hideEditBranch()" class="btn-secondary text-sm px-4 py-2">Batal</button>
                </div>
            </form>
        </div>

        {{-- Branches Table --}}
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Nama Cawangan</th>
                        <th>Telefon</th>
                        <th>Status</th>
                        <th class="text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $b)
                    <tr>
                        <td>
                            <span class="font-medium text-surface-800">{{ $b->name }}</span>
                            @if($b->address)
                                <p class="text-xs text-surface-400 mt-0.5">{{ Str::limit($b->address, 50) }}</p>
                            @endif
                        </td>
                        <td class="text-surface-600">{{ $b->phone ?? '-' }}</td>
                        <td>
                            <span class="badge-{{ $b->status === 'active' ? 'success' : 'warning' }}">
                                {{ $b->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <button onclick="showEditBranch({{ $b->id }}, '{{ addslashes($b->name) }}', '{{ addslashes($b->phone ?? '') }}', '{{ addslashes($b->status) }}', '{{ addslashes(str_replace(["\r","\n"], ' ', $b->address ?? '')) }}')" class="text-xs text-bonus-600 hover:text-bonus-800 mr-3">
                                ✏️ Edit
                            </button>
                            <button onclick="deleteBranch({{ $b->id }}, '{{ addslashes($b->name) }}')" class="text-xs text-red-500 hover:text-red-700">
                                🗑️ Padam
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-surface-400 py-8">
                            <div class="text-3xl mb-2">🏢</div>
                            <p>Tiada cawangan didaftarkan</p>
                            <p class="text-xs mt-1">Klik "Tambah Cawangan" untuk mula</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ── Profile Edit ─────────────────────────
function toggleEditProfile() {
    document.getElementById('profile-display').classList.toggle('hidden');
    document.getElementById('profile-edit').classList.toggle('hidden');
    document.getElementById('edit-profile-btn').classList.toggle('hidden');
}

async function saveProfile(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const res = await fetch('{{ route("merchant.profile.update") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({
                company_name: document.getElementById('edit-company').value,
                phone: document.getElementById('edit-phone').value,
                address: document.getElementById('edit-address').value
            })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('display-company').textContent = data.merchant.company_name;
            document.getElementById('display-phone').textContent = data.merchant.phone || '-';
            document.getElementById('display-address').textContent = data.merchant.address || '-';
            toggleEditProfile();
            showToast(data.message, 'success');
        }
    } catch(err) { showToast('Ralat menyimpan.', 'error'); }
    finally { btn.disabled = false; btn.textContent = '💾 Simpan'; }
}

// ── Branch Add ───────────────────────────
function showAddBranch() {
    document.getElementById('add-branch-form').classList.remove('hidden');
    document.getElementById('new-branch-name').focus();
}
function hideAddBranch() {
    document.getElementById('add-branch-form').classList.add('hidden');
    document.getElementById('add-branch-form').querySelector('form').reset();
}

async function saveBranch(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true;

    try {
        const res = await fetch('{{ route("merchant.branches.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({
                name: document.getElementById('new-branch-name').value,
                phone: document.getElementById('new-branch-phone').value,
                address: document.getElementById('new-branch-address').value
            })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            showToast(data.message || 'Ralat.', 'error');
        }
    } catch(err) { showToast('Ralat menyimpan.', 'error'); }
    finally { btn.disabled = false; }
}

// ── Branch Edit ──────────────────────────
function showEditBranch(id, name, phone, status, address) {
    document.getElementById('edit-branch-id').value = id;
    document.getElementById('edit-branch-name').value = name;
    document.getElementById('edit-branch-phone').value = phone;
    document.getElementById('edit-branch-status').value = status;
    document.getElementById('edit-branch-address').value = address;
    document.getElementById('edit-branch-form').classList.remove('hidden');
    document.getElementById('edit-branch-name').focus();
}
function hideEditBranch() {
    document.getElementById('edit-branch-form').classList.add('hidden');
}

async function updateBranch(e) {
    e.preventDefault();
    const id = document.getElementById('edit-branch-id').value;
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true;

    try {
        const res = await fetch('{{ route("merchant.branches.update", ["id" => "__ID__"]) }}'.replace('__ID__', id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({
                name: document.getElementById('edit-branch-name').value,
                phone: document.getElementById('edit-branch-phone').value,
                status: document.getElementById('edit-branch-status').value,
                address: document.getElementById('edit-branch-address').value
            })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            showToast(data.message || 'Ralat.', 'error');
        }
    } catch(err) { showToast('Ralat menyimpan.', 'error'); }
    finally { btn.disabled = false; }
}

// ── Branch Delete ────────────────────────
async function deleteBranch(id, name) {
    if (!confirm(`Padam cawangan "${name}"? Tindakan ini tidak boleh dibatalkan.`)) return;

    try {
        const res = await fetch('{{ route("merchant.branches.destroy", ["id" => "__ID__"]) }}'.replace('__ID__', id), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    } catch(err) { showToast('Ralat memadam.', 'error'); }
}

// ── Toast ────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm shadow-lg z-50 ' + (type === 'error' ? 'bg-red-500' : 'bg-emerald-500');
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
</script>
@endsection
