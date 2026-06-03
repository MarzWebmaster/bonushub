@extends('layouts.app')
@section('title', 'Edit Profil Syarikat')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">✏️ Edit Profil Syarikat</h1>
            <p class="text-surface-500 text-sm mt-1">Kemaskini maklumat syarikat anda</p>
        </div>
    </div>

    <div class="card max-w-2xl">
        <form action="{{ route('merchant.profile.update') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Syarikat <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" class="form-input @error('company_name') border-red-500 @enderror"
                           value="{{ old('company_name', $merchant->company_name) }}" required>
                    @error('company_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Telefon <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" class="form-input @error('phone') border-red-500 @enderror"
                           value="{{ old('phone', $merchant->phone) }}" required>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-input @error('address') border-red-500 @enderror"
                              rows="3">{{ old('address', $merchant->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-2 mt-6 pt-4 border-t border-surface-200">
                <button type="submit" class="btn-primary">
                    💾 Simpan
                </button>
                <a href="{{ route('merchant.profile') }}" class="btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
