@extends('layouts.app')
@section('title', 'Checkout - ' . $package->name)
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="max-w-xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('superadmin.packages') }}" class="text-sm text-surface-500 hover:text-bonus-600">← Back to Packages</a>
        </div>

        <div class="card overflow-hidden">
            {{-- Package Header --}}
            <div class="bg-gradient-to-r from-bonus-600 to-purple-600 p-6 text-white text-center">
                <div class="text-4xl mb-2">📦</div>
                <h1 class="text-2xl font-bold">{{ $package->name }}</h1>
                <div class="text-3xl font-bold mt-2">RM{{ number_format($package->price, 2) }}</div>
                <div class="text-sm opacity-80 mt-1">per year</div>
            </div>

            {{-- Package Details --}}
            <div class="p-6 space-y-4">
                <h3 class="font-semibold text-sm">Package Includes:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-green-500">✓</span>
                        <span>Branches: <strong>{{ $package->branch_limit == -1 ? 'Unlimited' : $package->branch_limit }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-500">✓</span>
                        <span>Staff: <strong>{{ $package->staff_limit == -1 ? 'Unlimited' : $package->staff_limit }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-500">✓</span>
                        <span>Giveaways: <strong>{{ $package->giveaway_limit == -1 ? 'Unlimited' : $package->giveaway_limit }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-500">✓</span>
                        <span>Viral Tasks: <strong>{{ $package->task_limit == -1 ? 'Unlimited' : $package->task_limit }}</strong></span>
                    </div>
                </div>

                {{-- Features --}}
                @if($package->features)
                <div class="border-t border-surface-100 pt-4">
                    <h3 class="font-semibold text-sm mb-2">Features:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($package->features as $feature => $enabled)
                            @if($enabled)
                            <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Payment Button --}}
                <div class="border-t border-surface-100 pt-4">
                    <form method="POST" action="{{ route('payment.create-session', $package) }}">
                        @csrf
                        <button type="submit" id="pay-btn" class="w-full btn-primary py-3 text-base flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Pay RM{{ number_format($package->price, 2) }} with Stripe
                        </button>
                    </form>
                    <p class="text-xs text-surface-400 text-center mt-3">Secure payment powered by Stripe. We accept Visa, Mastercard, and FPX.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection