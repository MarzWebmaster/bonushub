@extends('layouts.app')
@section('title', 'Staff - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Staff</h1>
            <p class="page-subtitle">People who award points and process redemptions at your counter
                — {{ $used }} active of {{ $limit < 0 ? 'unlimited' : $limit }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="card p-4 sm:p-6 mb-6">
        <h2 class="font-bold text-surface-800 mb-4">Add staff</h2>
        <form method="POST" action="{{ route('merchant.staff.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="form-label">Name</label><input name="name" class="form-input" value="{{ old('name') }}" required></div>
                <div><label class="form-label">Email (login)</label><input name="email" type="email" class="form-input" value="{{ old('email') }}" required></div>
                <div><label class="form-label">Phone (optional)</label><input name="phone" class="form-input" value="{{ old('phone') }}"></div>
                <div><label class="form-label">Branch</label>
                    <select name="branch_id" class="form-input">
                        <option value="">All / head office</option>
                        @foreach($branches as $b)<option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>@endforeach
                    </select></div>
                <div><label class="form-label">Temporary password (min 8)</label><input name="password" type="password" class="form-input" minlength="8" required autocomplete="new-password"></div>
                <div><label class="form-label">Confirm password</label><input name="password_confirmation" type="password" class="form-input" minlength="8" required autocomplete="new-password"></div>
            </div>
            <button type="submit" class="btn-primary" @disabled($limit >= 0 && $used >= $limit)>Add staff</button>
            @if($limit >= 0 && $used >= $limit)
                <p class="text-xs text-red-600">Your package allows {{ $limit }} active staff. Deactivate someone or upgrade your package.</p>
            @endif
        </form>
    </div>

    <div class="card p-4 sm:p-6">
        <h2 class="font-bold text-surface-800 mb-4">Your staff</h2>
        @forelse($staff as $s)
            <div class="py-4 border-t border-gray-100 first:border-t-0">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div>
                        <p class="font-medium text-surface-800">{{ $s->name }}
                            @if($s->status === 'inactive')<span class="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Inactive</span>
                            @else<span class="ml-2 text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded">Active</span>@endif
                        </p>
                        <p class="text-xs text-surface-500">{{ $s->email }} · {{ $s->branch->name ?? 'All / head office' }}</p>
                    </div>
                    <form method="POST" action="{{ route('merchant.staff.toggle', $s->id) }}" onsubmit="return confirm('{{ $s->status === 'inactive' ? 'Re-activate' : 'Deactivate' }} {{ addslashes($s->name) }}?')">
                        @csrf
                        <button type="submit" class="{{ $s->status === 'inactive' ? 'btn-outline' : 'btn-danger' }} px-3 py-1.5 text-sm">{{ $s->status === 'inactive' ? 'Re-activate' : 'Deactivate' }}</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('merchant.staff.update', $s->id) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                    @csrf @method('PUT')
                    <div><label class="form-label">Name</label><input name="name" class="form-input" value="{{ $s->name }}" required></div>
                    <div><label class="form-label">Phone</label><input name="phone" class="form-input" value="{{ $s->phone }}"></div>
                    <div><label class="form-label">Branch</label>
                        <select name="branch_id" class="form-input">
                            <option value="">All / head office</option>
                            @foreach($branches as $b)<option value="{{ $b->id }}" @selected($s->branch_id == $b->id)>{{ $b->name }}</option>@endforeach
                        </select></div>
                    <div><label class="form-label">New password (optional)</label><input name="password" type="password" class="form-input" minlength="8" autocomplete="new-password"></div>
                    <div class="sm:col-span-4 flex items-center gap-3">
                        <input name="password_confirmation" type="password" class="form-input" style="max-width:16rem" minlength="8" placeholder="Confirm new password" autocomplete="new-password">
                        <button type="submit" class="btn-primary px-4 py-2 text-sm">Save</button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-surface-500">No staff yet. Add your first team member above.</p>
        @endforelse
    </div>
</div>
@endsection
