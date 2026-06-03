@extends('layouts.app')
@section('title', 'Tambah Cawangan')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">➕ Tambah Cawangan</h1>
            <p class="text-surface-500 text-sm mt-1">Daftar cawangan baru untuk {{ $merchant->company_name }}</p>
        </div>
    </div>

    <div class="card max-w-2xl">
        <form action="{{ route('merchant.branches.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Cawangan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-500 @enderror"
                           value="{{ old('name') }}" placeholder="Cth: Cawangan Puncak Alam" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-input @error('phone') border-red-500 @enderror"
                           value="{{ old('phone') }}" placeholder="013XXXXXXX">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-input @error('address') border-red-500 @enderror"
                              rows="3" placeholder="Alamat cawangan...">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-2 mt-6 pt-4 border-t border-surface-200">
                <button type="submit" class="btn-primary">
                    💾 Simpan Cawangan
                </button>
                <a href="{{ route('merchant.profile') }}" class="btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
