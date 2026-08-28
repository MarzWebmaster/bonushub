@extends('layouts.guest')
@section('title', 'Pengesahan Merchant')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="card p-8 shadow-xl">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Pengesahan Dokumen</h1>
                <p class="text-surface-500 mt-1">Muat naik IC & SSM untuk pengesahan akaun merchant</p>
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

            <form action="{{ route('merchant.verification.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- IC Upload --}}
                <div>
                    <label class="form-label">Gambar IC (MyKad)</label>
                    <p class="text-xs text-surface-400 mb-2">Muat naik gambar depan dan belakang IC anda</p>
                    <div class="relative border-2 border-dashed border-surface-200 rounded-lg p-6 text-center hover:border-emerald-400 transition-colors" id="ic-dropzone">
                        <input type="file" name="ic_image" id="ic_input" accept="image/jpeg,image/png,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                        <div id="ic-preview" class="hidden">
                            <img id="ic-preview-img" class="mx-auto h-32 object-contain rounded" alt="IC Preview">
                            <p class="text-sm text-surface-600 mt-2" id="ic-filename"></p>
                        </div>
                        <div id="ic-placeholder">
                            <svg class="w-12 h-12 mx-auto text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm text-surface-500 mt-2">Klik atau seret fail IC ke sini</p>
                            <p class="text-xs text-surface-400 mt-1">JPG, PNG, atau PDF (Maks. 5MB)</p>
                        </div>
                    </div>
                    @error('ic_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- SSM Upload --}}
                <div>
                    <label class="form-label">Gambar SSM (Pendaftaran Perniagaan)</label>
                    <p class="text-xs text-surface-400 mb-2">Muat naik salinan SSM / Borang 9 / Borang 49</p>
                    <div class="relative border-2 border-dashed border-surface-200 rounded-lg p-6 text-center hover:border-emerald-400 transition-colors" id="ssm-dropzone">
                        <input type="file" name="ssm_image" id="ssm_input" accept="image/jpeg,image/png,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                        <div id="ssm-preview" class="hidden">
                            <img id="ssm-preview-img" class="mx-auto h-32 object-contain rounded" alt="SSM Preview">
                            <p class="text-sm text-surface-600 mt-2" id="ssm-filename"></p>
                        </div>
                        <div id="ssm-placeholder">
                            <svg class="w-12 h-12 mx-auto text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm text-surface-500 mt-2">Klik atau seret fail SSM ke sini</p>
                            <p class="text-xs text-surface-400 mt-1">JPG, PNG, atau PDF (Maks. 5MB)</p>
                        </div>
                    </div>
                    @error('ssm_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- PDPA Consent --}}
                <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="consent_pdpa" value="1" class="mt-1 h-4 w-4 text-emerald-600 border-surface-300 rounded focus:ring-emerald-500" required>
                        <div>
                            <p class="text-sm font-medium text-surface-700">Persetujuan PDPA</p>
                            <p class="text-xs text-surface-500 mt-1">
                                Saya bersetuju dengan <strong>Dasar Perlindungan Data Peribadi (PDPA)</strong> BonusHub.
                                Maklumat peribadi saya akan digunakan untuk tujuan pengesahan dan pengurusan akaun merchant sahaja.
                            </p>
                        </div>
                    </label>
                    @error('consent_pdpa') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col gap-3">
                    <button type="submit" class="btn-primary w-full py-3 text-base flex items-center justify-center gap-2">
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
                ⚠️ Anda boleh mengedit profil sahaja sehingga dokumen disahkan oleh admin.
            </p>
        </div>
    </div>
</div>

<script>
// IC image preview
document.getElementById('ic_input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('ic-preview-img').src = ev.target.result;
            document.getElementById('ic-filename').textContent = file.name;
            document.getElementById('ic-preview').classList.remove('hidden');
            document.getElementById('ic-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
});

// SSM image preview
document.getElementById('ssm_input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('ssm-preview-img').src = ev.target.result;
            document.getElementById('ssm-filename').textContent = file.name;
            document.getElementById('ssm-preview').classList.remove('hidden');
            document.getElementById('ssm-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
});

// Skip verification
async function skipVerification() {
    if (confirm('Anda pasti nak langkau pengesahan? Anda hanya boleh mengedit profil sahaja sehingga dokumen dimuat naik.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("merchant.verification.skip") }}';
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
