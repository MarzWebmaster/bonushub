@extends('layouts.guest')
@section('title', 'Daftar Merchant')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="card p-8 shadow-xl">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Daftar Sebagai Merchant</h1>
                <p class="text-surface-500 mt-1">Mula program loyalty untuk bisnes anda</p>
                <p class="text-xs text-surface-400 mt-1">✅ Percuma • ✅ Aktif serta-merta • ✅ Tiada approval</p>
            </div>

            <form action="{{ route('merchant.register.post') }}" method="POST" class="space-y-4" id="merchant-register-form">
                @csrf
                <input type="hidden" name="_t" value="{{ time() }}">

                {{-- 🪤 HONEYPOT — hidden from humans, visible to bots --}}
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                {{-- Company Name --}}
                <div>
                    <label class="form-label">Nama Syarikat</label>
                    <input type="text" name="company_name" class="form-input" placeholder="Contoh: Kedai Kopi Ali Sdn Bhd" required autofocus value="{{ old('company_name') }}">
                    @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Your Name --}}
                <div>
                    <label class="form-label">Nama Anda</label>
                    <input type="text" name="name" class="form-input" placeholder="Ali bin Abu" required value="{{ old('name') }}">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="ali@kedaikopi.com" required value="{{ old('email') }}">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="form-label">No. Telefon</label>
                    <input type="text" name="phone" class="form-input" placeholder="0123456789" required value="{{ old('phone') }}">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="form-label">Kata Laluan</label>
                    <input type="password" name="password" class="form-input" placeholder="Min. 8 aksara" required>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="form-label">Sahkan Kata Laluan</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Ulang kata laluan" required>
                </div>

                {{-- Error summary --}}
                @if($errors->any())
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <button type="submit" class="btn-primary w-full py-3 text-base flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Aktifkan Akaun Merchant
                </button>
            </form>

            <p class="text-center text-sm text-surface-500 mt-6">
                Dah ada akaun? <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Log Masuk</a>
            </p>

            <p class="text-center text-xs text-surface-400 mt-3">
                Pelanggan? <a href="{{ route('register') }}" class="text-bonus-600 hover:text-bonus-700">Daftar sebagai pengguna</a>
            </p>
        </div>
    </div>
</div>
@endsection
