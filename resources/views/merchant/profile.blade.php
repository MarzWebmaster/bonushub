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

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Company Info Card --}}
    <div class="card mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-surface-800">Maklumat Syarikat</h2>
            <a href="{{ route('merchant.profile.edit') }}" class="btn-primary text-sm px-4 py-2">
                ✏️ Edit
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-surface-400 uppercase tracking-wider">Nama Syarikat</label>
                <p class="text-surface-800 font-medium">{{ $merchant->company_name }}</p>
            </div>
            <div>
                <label class="text-xs text-surface-400 uppercase tracking-wider">Telefon</label>
                <p class="text-surface-800 font-medium">{{ $merchant->phone ?? '-' }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="text-xs text-surface-400 uppercase tracking-wider">Alamat</label>
                <p class="text-surface-800 font-medium">{{ $merchant->address ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Branches Card --}}
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-surface-800">Cawangan ({{ $branches->count() }})</h2>
            <a href="{{ route('merchant.branches.create') }}" class="btn-primary text-sm px-4 py-2">
                + Tambah Cawangan
            </a>
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
                            <a href="{{ route('merchant.branches.edit', $b->id) }}" class="text-xs text-bonus-600 hover:text-bonus-800 mr-3">
                                ✏️ Edit
                            </a>
                            <form action="{{ route('merchant.branches.destroy', $b->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Padam cawangan &quot;{{ addslashes($b->name) }}&quot;? Tindakan tidak boleh dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 bg-transparent border-none cursor-pointer p-0">
                                    🗑️ Padam
                                </button>
                            </form>
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
