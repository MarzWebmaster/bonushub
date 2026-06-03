@extends('layouts.app')
@section('title', 'Edit Cawangan')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">✏️ Edit Cawangan</h1>
            <p class="text-surface-500 text-sm mt-1">{{ $branch->name }} — {{ $merchant->company_name }}</p>
        </div>
    </div>

    <div class="card max-w-2xl">
        <form action="{{ route('merchant.branches.update', $branch->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Cawangan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-500 @enderror"
                           value="{{ old('name', $branch->name) }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-input @error('phone') border-red-500 @enderror"
                           value="{{ old('phone', $branch->phone) }}">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input @error('status') border-red-500 @enderror">
                        <option value="active" {{ old('status', $branch->status) === 'active' ? 'selected' : '' }}>🟢 Aktif</option>
                        <option value="inactive" {{ old('status', $branch->status) === 'inactive' ? 'selected' : '' }}>🟡 Tidak Aktif</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-input @error('address') border-red-500 @enderror"
                              rows="3">{{ old('address', $branch->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Danger zone: delete button --}}
                <div class="mt-6 pt-4 border-t border-surface-200">
                    <p class="text-xs text-surface-400 mb-2">Zon Bahaya</p>
                    <form action="{{ route('merchant.branches.destroy', $branch->id) }}" method="POST"
                          onsubmit="return confirm('Padam cawangan ini? Tindakan tidak boleh dibatalkan.')"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger text-sm">
                            🗑️ Padam Cawangan
                        </button>
                    </form>
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
