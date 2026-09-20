@extends('layouts.guest')
@section('title', 'Pengesahan Merchant')
@section('content')
<style>
.upload-zone{position:relative;border:2px dashed #e5e7eb;border-radius:12px;padding:20px;text-align:center;transition:all .2s;cursor:pointer;background:#fafafa}
.upload-zone:hover{border-color:#6366f1;background:#f5f3ff}
.upload-zone.dragover{border-color:#6366f1;background:#ede9fe}
.upload-zone.has-file{border-color:#10b981;background:#f0fdf4;border-style:solid}
.upload-zone input[type=file]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
.upload-zone .icon{font-size:32px;margin-bottom:8px}
.upload-zone .text{font-size:13px;color:#6b7280}
.upload-zone .filename{font-size:12px;color:#10b981;font-weight:600;margin-top:4px}
.upload-zone .preview-img{max-height:100px;border-radius:8px;margin:8px auto;object-fit:contain}
.ic-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:480px){.ic-grid{grid-template-columns:1fr}}
</style>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="card p-6 sm:p-8 shadow-xl">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Pengesahan Dokumen</h1>
                <p class="text-surface-500 mt-1">Muat naik IC (depan & belakang) dan SSM</p>
            </div>

            @if(session('success'))
                <div class="p-4 rounded-lg bg-green-50 border border-green-200 mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm text-green-700 font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm text-red-700 font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('merchant.verification.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5" id="verifyForm">
                @csrf

                {{-- IC Depan & Belakang --}}
                <div>
                    <label class="form-label font-semibold">Gambar IC (MyKad)</label>
                    <p class="text-xs text-surface-400 mb-3">Muat naik gambar depan dan belakang IC anda</p>
                    <div class="ic-grid">
                        {{-- IC Depan --}}
                        <div class="upload-zone" id="ic-front-zone">
                            <input type="file" name="ic_image_front" accept="image/jpeg,image/png,application/pdf" required>
                            <div class="preview hidden">
                                <img class="preview-img" alt="IC Depan">
                                <p class="filename"></p>
                            </div>
                            <div class="placeholder">
                                <div class="icon">🪪</div>
                                <div class="text font-medium">IC Depan</div>
                                <div class="text" style="font-size:11px">Klik untuk pilih fail</div>
                            </div>
                        </div>
                        {{-- IC Belakang --}}
                        <div class="upload-zone" id="ic-back-zone">
                            <input type="file" name="ic_image_back" accept="image/jpeg,image/png,application/pdf" required>
                            <div class="preview hidden">
                                <img class="preview-img" alt="IC Belakang">
                                <p class="filename"></p>
                            </div>
                            <div class="placeholder">
                                <div class="icon">🪪</div>
                                <div class="text font-medium">IC Belakang</div>
                                <div class="text" style="font-size:11px">Klik untuk pilih fail</div>
                            </div>
                        </div>
                    </div>
                    @error('ic_image_front') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('ic_image_back') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- SSM --}}
                <div>
                    <label class="form-label font-semibold">Gambar SSM (Pendaftaran Perniagaan)</label>
                    <p class="text-xs text-surface-400 mb-2">Muat naik salinan SSM / Borang 9 / Borang 49</p>
                    <div class="upload-zone" id="ssm-zone">
                        <input type="file" name="ssm_image" accept="image/jpeg,image/png,application/pdf" required>
                        <div class="preview hidden">
                            <img class="preview-img" alt="SSM Preview">
                            <p class="filename"></p>
                        </div>
                        <div class="placeholder">
                            <div class="icon">📄</div>
                            <div class="text font-medium">Dokumen SSM</div>
                            <div class="text" style="font-size:11px">Klik untuk pilih fail</div>
                        </div>
                    </div>
                    @error('ssm_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- PDPA --}}
                <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="consent_pdpa" value="1" class="mt-1 h-4 w-4 text-emerald-600 border-surface-300 rounded focus:ring-emerald-500" required>
                        <div>
                            <p class="text-sm font-medium text-surface-700">Persetujuan PDPA</p>
                            <p class="text-xs text-surface-500 mt-1">Saya bersetuju dengan <strong>Dasar Perlindungan Data Peribadi (PDPA)</strong> BonusHub. Maklumat peribadi saya akan digunakan untuk tujuan pengesahan sahaja.</p>
                        </div>
                    </label>
                    @error('consent_pdpa') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col gap-3">
                    <button type="submit" id="submitBtn" class="btn-primary w-full py-3 text-base flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        Hantar Dokumen
                    </button>
                    <button type="button" onclick="skipVerification()" class="w-full py-3 text-base flex items-center justify-center gap-2 text-surface-500 hover:text-surface-700 border border-surface-200 rounded-lg hover:bg-surface-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Langkau Buat Sementara
                    </button>
                </div>
            </form>

            <p class="text-center text-xs text-surface-400 mt-4">
                Anda boleh mengedit profil sahaja sehingga dokumen disahkan oleh admin.
            </p>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.upload-zone input[type=file]').forEach(input => {
    const zone = input.closest('.upload-zone');
    
    // Drag & drop
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        if(e.dataTransfer.files.length) { input.files = e.dataTransfer.files; handleFile(input, zone); }
    });

    input.addEventListener('change', () => handleFile(input, zone));
});

function handleFile(input, zone) {
    const file = input.files[0];
    if(!file) return;
    
    zone.classList.add('has-file');
    const preview = zone.querySelector('.preview');
    const placeholder = zone.querySelector('.placeholder');
    const img = preview.querySelector('.preview-img');
    const fname = preview.querySelector('.filename');
    
    fname.textContent = file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name;
    
    if(file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; img.classList.remove('hidden'); };
        reader.readAsDataURL(file);
    } else {
        img.classList.add('hidden');
    }
    
    preview.classList.remove('hidden');
    placeholder.classList.add('hidden');
}

// Submit button loading state
document.getElementById('verifyForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menghantar...';
});

async function skipVerification() {
    if(confirm('Anda pasti nak langkau pengesahan?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("merchant.verification.skip") }}';
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
