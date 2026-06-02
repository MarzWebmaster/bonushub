@extends('layouts.guest')
@section('title', 'Register')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="card p-8 shadow-xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Join BonusHub</h1>
                <p class="text-surface-500 mt-1">Create your account to start earning rewards</p>

                @if($campaign)
                <div class="mt-4 p-3 rounded-lg bg-gradient-to-r from-bonus-50 to-purple-50 border border-bonus-200">
                    <div class="flex items-center justify-center gap-2 text-sm">
                        <span class="text-lg">🎯</span>
                        <span class="font-semibold text-bonus-700">{{ $campaign->merchant->company_name ?? 'Merchant' }}</span>
                        <span class="text-surface-600">— {{ $campaign->name }}</span>
                    </div>
                    <p class="text-xs text-surface-500 mt-1">Register now to join this merchant's loyalty program!</p>
                </div>
                @endif
            </div>

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                @if($ref)
                    <input type="hidden" name="ref" value="{{ $ref }}">
                @endif

                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" placeholder="Ali bin Abu" required autofocus value="{{ old('name') }}">
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="ali@email.com" required value="{{ old('email') }}">
                </div>

                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" placeholder="0123456789" required value="{{ old('phone') }}">
                </div>

                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>

                <div>
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
                </div>

                @if($errors->any())
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <button type="submit" class="btn-primary w-full py-3 text-base">Create Account</button>
            </form>

            <p class="text-center text-sm text-surface-500 mt-6">
                Already have an account? <a href="{{ route('login') }}" class="text-bonus-600 hover:text-bonus-700 font-medium">Sign In</a>
            </p>
        </div>
    </div>
</div>
@endsection
