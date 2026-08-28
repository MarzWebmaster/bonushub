@extends('layouts.app')

@section('title', 'Merchant Menunggu Pengesahan')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-surface-800">Merchant Menunggu Pengesahan</h1>
            <p class="text-surface-500 mt-1">Semak dan luluskan merchant yang telah mendaftar</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-sm font-medium">
                {{ $merchants->count() }} menunggu
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="text-green-700 font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($merchants->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-lg font-semibold text-surface-700 mt-4">Tiada Merchant Menunggu</h3>
            <p class="text-surface-500 mt-1">Semua merchant telah pun disahkan.</p>
        </div>
    @else
        {{-- Merchant Cards --}}
        <div class="grid gap-4">
            @foreach($merchants as $merchant)
                <div class="card p-6" id="merchant-{{ $merchant->id }}">
                    <div class="flex items-start justify-between">
                        {{-- Merchant Info --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg shrink-0">
                                {{ strtoupper(substr($merchant->company_name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-surface-800">{{ $merchant->company_name }}</h3>
                                <p class="text-sm text-surface-500">Pemilik: {{ $merchant->owner_name ?? $merchant->users->first()->name ?? '-' }}</p>
                                <p class="text-sm text-surface-500">Email: {{ $merchant->users->first()->email ?? '-' }}</p>
                                <p class="text-sm text-surface-500">Phone: {{ $merchant->phone ?? '-' }}</p>
                                <p class="text-xs text-surface-400 mt-1">
                                    Didaftarkan: {{ $merchant->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
                            🟡 Menunggu
                        </span>
                    </div>

                    {{-- Document Links --}}
                    <div class="mt-4 flex items-center gap-3">
                        @if($merchant->ic_image)
                            <a href="{{ Storage::url($merchant->ic_image) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-sm font-medium hover:bg-blue-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                                Lihat IC
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-400 text-sm">
                                ❌ IC belum dimuat naik
                            </span>
                        @endif

                        @if($merchant->ssm_image)
                            <a href="{{ Storage::url($merchant->ssm_image) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-sm font-medium hover:bg-blue-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Lihat SSM
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-400 text-sm">
                                ❌ SSM belum dimuat naik
                            </span>
                        @endif

                        <span class="text-xs text-surface-400">
                            @if($merchant->consent_pdpa)
                                ✅ PDPA diterima
                            @else
                                ⚠️ PDPA belum diterima
                            @endif
                        </span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 flex items-center gap-3 pt-4 border-t border-surface-100">
                        <button onclick="approveMerchant({{ $merchant->id }}, '{{ addslashes($merchant->company_name) }}')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Luluskan
                        </button>

                        <button onclick="rejectMerchant({{ $merchant->id }}, '{{ addslashes($merchant->company_name) }}')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium transition-colors border border-red-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak
                        </button>

                        <a href="{{ route('superadmin.merchants.show', $merchant->id) }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-surface-500 hover:text-surface-700 text-sm transition-colors">
                            Lihat Detail →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Reject Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeRejectModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-lg font-semibold text-surface-800">Tolak Merchant</h3>
            <p class="text-sm text-surface-500 mt-1">Merchant: <strong id="reject-merchant-name"></strong></p>

            <div class="mt-4">
                <label class="form-label">Sebab Penolakan</label>
                <textarea id="reject-reason" class="form-input" rows="3" placeholder="Contoh: IC kabur, SSM tidak lengkap, dll."></textarea>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <button onclick="closeRejectModal()" class="flex-1 px-4 py-2 rounded-lg border border-surface-200 text-surface-600 hover:bg-surface-50 text-sm font-medium">
                    Batal
                </button>
                <button onclick="submitReject()" class="flex-1 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-medium">
                    Tolak Merchant
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let rejectId = null;

async function approveMerchant(id, name) {
    if (!confirm(`Anda pasti nak LULUSKAN merchant "${name}"?`)) return;

    try {
        const response = await fetch(`/superadmin/api/merchants/${id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            // Remove card with animation
            const card = document.getElementById(`merchant-${id}`);
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(50px)';
            setTimeout(() => card.remove(), 300);

            // Show success toast
            showToast('success', `✅ ${name} telah diluluskan! Notifikasi dihantar.`);
        } else {
            alert('Gagal: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Ralat jaringan. Sila cuba lagi.');
    }
}

function rejectMerchant(id, name) {
    rejectId = id;
    document.getElementById('reject-merchant-name').textContent = name;
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    rejectId = null;
    document.getElementById('reject-reason').value = '';
}

async function submitReject() {
    if (!rejectId) return;

    const reason = document.getElementById('reject-reason').value;

    try {
        const response = await fetch(`/superadmin/api/merchants/${rejectId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason }),
        });

        const data = await response.json();

        if (data.success) {
            const card = document.getElementById(`merchant-${rejectId}`);
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(-50px)';
            setTimeout(() => card.remove(), 300);

            closeRejectModal();
            showToast('error', `❌ Merchant telah ditolak.`);
        } else {
            alert('Gagal: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Ralat jaringan. Sila cuba lagi.');
    }
}

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium transition-all transform ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
