@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="card p-8 shadow-xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Welcome Back</h1>
                <p class="text-surface-500 mt-1">Sign in to your BonusHub account</p>
            </div>
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div><label class="form-label">Email</label><input type="email" name="email" class="form-input" placeholder="admin@example.com" required autofocus></div>
                <div><label class="form-label">Password</label><input type="password" name="password" class="form-input" placeholder="••••••••" required></div>
                @if($errors->any())
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="btn-primary w-full py-3 text-base">Sign In</button>
            </form>
        </div>
    </div>
</div>
@endsection