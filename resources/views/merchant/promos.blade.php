@extends('layouts.app')
@section('title', 'Promos - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">🎉 Promos</h1>
            <p class="text-surface-500 text-sm mt-1">Create registration bonuses, multipliers & more</p>
        </div>
        <button onclick="showCreate()" class="btn-primary text-sm px-4 py-2">+ New Promo</button>
    </div>

    {{-- Flash Messages --}}
    <div id="flash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

    {{-- Create/Edit Modal --}}
    <div id="promo-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="card w-full max-w-lg mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-surface-800" id="modal-title">New Promo</h3>
                <button onclick="hideModal()" class="text-surface-400 hover:text-surface-600 text-xl">&times;</button>
            </div>
            <form id="promo-form" onsubmit="savePromo(event)" class="space-y-4">
                @csrf
                <input type="hidden" name="id" id="promo-id">
                <div>
                    <label class="form-label">Promo Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="promo-name" class="form-input" placeholder="Cth: Double Points Weekend" required>
                </div>
                <div>
                    <label class="form-label">Type <span class="text-red-500">*</span></label>
                    <select name="type" id="promo-type" class="form-input" required>
                        <option value="registration_bonus">Registration Bonus (points for new join)</option>
                        <option value="multiplier">Multiplier (e.g. 2x points)</option>
                        <option value="fixed_bonus">Fixed Bonus</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Value <span class="text-red-500">*</span></label>
                    <input type="number" name="value" id="promo-value" class="form-input" step="0.1" min="0" placeholder="100 (points) or 2.0 (multiplier)" required>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" id="promo-status" class="form-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Start (optional)</label>
                        <input type="datetime-local" name="starts_at" id="promo-starts" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">End (optional)</label>
                        <input type="datetime-local" name="ends_at" id="promo-ends" class="form-input">
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary text-sm px-4 py-2" id="save-btn">💾 Simpan</button>
                    <button type="button" onclick="hideModal()" class="btn-secondary text-sm px-4 py-2">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Promos List --}}
    <div class="card">
        <div class="overflow-x-auto"><table class="data-table">
            <thead>
                <tr>
                    <th>Promo</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="promo-table">
                @forelse($promos as $p)
                <tr id="row-{{ $p->id }}">
                    <td class="font-medium text-surface-800">{{ $p->name }}</td>
                    <td>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($p->type === 'registration_bonus') bg-blue-50 text-blue-700
                            @elseif($p->type === 'multiplier') bg-purple-50 text-purple-700
                            @else bg-amber-50 text-amber-700 @endif">
                            {{ str_replace('_', ' ', $p->type) }}
                        </span>
                    </td>
                    <td class="font-bold text-surface-700">
                        @if($p->type === 'multiplier')
                            {{ $p->value }}x
                        @else
                            {{ number_format($p->value) }} pts
                        @endif
                    </td>
                    <td>
                        <span class="badge-{{ $p->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td class="text-xs text-surface-400">
                        @if($p->starts_at || $p->ends_at)
                            {{ $p->starts_at ? $p->starts_at->format('d M Y') : '...' }}
                            —
                            {{ $p->ends_at ? $p->ends_at->format('d M Y') : '...' }}
                        @else
                            Forever
                        @endif
                    </td>
                    <td class="text-right">
                        <button onclick="showEdit({{ $p->id }})" class="text-xs text-bonus-600 hover:text-bonus-800 mr-3">✏️ Edit</button>
                        <button onclick="deletePromo({{ $p->id }}, '{{ addslashes($p->name) }}')" class="text-xs text-red-500 hover:text-red-700">🗑️ Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-surface-400 py-8">No promos yet. Create one above!</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
</div>

<script>
const allPromos = @json($promos);

function showCreate() {
    document.getElementById('promo-id').value = '';
    document.getElementById('modal-title').textContent = 'New Promo';
    document.getElementById('promo-form').reset();
    document.getElementById('promo-type').value = 'registration_bonus';
    document.getElementById('promo-status').value = 'active';
    document.getElementById('promo-modal').classList.remove('hidden');
}

function showEdit(id) {
    const p = allPromos.find(x => x.id === id);
    if (!p) return;
    document.getElementById('promo-id').value = p.id;
    document.getElementById('modal-title').textContent = 'Edit Promo';
    document.getElementById('promo-name').value = p.name;
    document.getElementById('promo-type').value = p.type;
    document.getElementById('promo-value').value = p.value;
    document.getElementById('promo-status').value = p.status;
    document.getElementById('promo-starts').value = p.starts_at ? p.starts_at.replace(' ', 'T').slice(0,16) : '';
    document.getElementById('promo-ends').value = p.ends_at ? p.ends_at.replace(' ', 'T').slice(0,16) : '';
    document.getElementById('promo-modal').classList.remove('hidden');
}

function hideModal() {
    document.getElementById('promo-modal').classList.add('hidden');
}

async function savePromo(e) {
    e.preventDefault();
    const id = document.getElementById('promo-id').value;
    const btn = document.getElementById('save-btn');
    btn.disabled = true; btn.textContent = 'Saving...';

    const body = {
        name: document.getElementById('promo-name').value,
        type: document.getElementById('promo-type').value,
        value: document.getElementById('promo-value').value,
        status: document.getElementById('promo-status').value,
        starts_at: document.getElementById('promo-starts').value || null,
        ends_at: document.getElementById('promo-ends').value || null,
    };

    try {
        const method = id ? 'PUT' : 'POST';
        const url = id ? '/merchant/api/promos/' + id : '/merchant/api/promos';
        const res = await fetch(url, {
            method,
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify(body)
        });
        const d = await res.json();
        if (d.success) {
            window.location.reload();
        } else {
            flash(d.message || 'Error', 'error');
        }
    } catch(err) { flash('Network error', 'error'); }
    finally { btn.disabled = false; btn.textContent = '💾 Simpan'; }
}

async function deletePromo(id, name) {
    if (!confirm('Delete promo "' + name + '"?')) return;
    try {
        const res = await fetch('/merchant/api/promos/' + id, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
        });
        const d = await res.json();
        if (d.success) window.location.reload();
        else flash(d.message || 'Error', 'error');
    } catch(err) { flash('Network error', 'error'); }
}

function flash(msg, type) {
    const el = document.getElementById('flash');
    el.textContent = msg;
    el.className = 'mb-4 px-4 py-3 rounded-lg text-sm ' + (type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700');
    el.classList.remove('hidden');
}
</script>
@endsection
