@extends('layouts.app')
@section('title', 'Scan & Earn Points')
@section('content')
<div class="page-container" style="padding-top:0;max-width:480px;margin:0 auto;">
    <div class="page-header">
        <div class="text-center">
            <h1 class="page-title">📱 Scan & Earn</h1>
            <p class="page-subtitle">{{ $merchant->company_name }}</p>
            @if($branch)
                <p class="text-sm text-surface-500">📍 {{ $branch->name }}</p>
            @endif
        </div>
    </div>

    <!-- Not Logged In -->
    @guest
    <div class="card p-6 text-center">
        <h2 class="font-bold text-lg mb-4">Login Required</h2>
        <p class="text-surface-600 mb-6">Please login to earn points from {{ $merchant->company_name }}</p>
        <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn-primary w-full">Login to Earn Points</a>
        <p class="text-sm text-surface-500 mt-4">Don't have an account? <a href="{{ route('register') }}" class="text-primary hover:underline">Register here</a></p>
    </div>
    @else
    <!-- Logged In — Earn Points Form -->
    <div class="card p-6" x-data="earnApp()" x-init="init()">

        <!-- Success State -->
        <div x-show="success" class="text-center py-8">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="font-bold text-xl text-primary mb-2" x-text="message"></h2>
            <div class="text-surface-600 space-y-2">
                <p>Points earned: <strong x-text="pointsEarned"></strong></p>
                @if($branch)
                <p class="text-sm">Branch: {{ $branch->name }}</p>
                @endif
                <p>New balance: <strong x-text="newBalance"></strong> pts</p>
                <p class="text-xs text-surface-400 mt-2" x-text="'at ' + merchantName"></p>
            </div>
            <button @click="resetForm()" class="btn-primary mt-6 w-full">Done</button>
        </div>

        <!-- Error State -->
        <div x-show="error && !success" class="text-center py-6">
            <div class="text-4xl mb-4">❌</div>
            <h2 class="font-bold text-lg text-red-600 mb-2">Error</h2>
            <p class="text-surface-600" x-text="error"></p>
            <button @click="error=''" class="btn-outline mt-4">Try Again</button>
            <a x-show="redirectUrl" :href="redirectUrl" class="btn-primary mt-2 w-full">Join Merchant</a>
        </div>

        <!-- Form -->
        <div x-show="!success && !error">
            <h2 class="font-bold text-lg text-surface-800 mb-2">💰 Enter Amount Spent</h2>
            <p class="text-sm text-surface-500 mb-6">How much did you spend at {{ $merchant->company_name }}?</p>

            <form @submit.prevent="earnPoints()">
                <div class="mb-4">
                    <label class="form-label">Amount (RM)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-surface-500 font-semibold">RM</span>
                        <input type="number" x-model="amount" step="0.01" min="0.01" max="999999.99"
                            class="form-input pl-10 text-2xl font-bold text-center py-4"
                            placeholder="0.00" required autofocus>
                    </div>
                </div>

                <!-- Quick Amount Buttons -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button" @click="amount=10" class="btn-outline py-2 text-sm">RM10</button>
                    <button type="button" @click="amount=20" class="btn-outline py-2 text-sm">RM20</button>
                    <button type="button" @click="amount=50" class="btn-outline py-2 text-sm">RM50</button>
                    <button type="button" @click="amount=100" class="btn-outline py-2 text-sm">RM100</button>
                    <button type="button" @click="amount=200" class="btn-outline py-2 text-sm">RM200</button>
                    <button type="button" @click="amount=500" class="btn-outline py-2 text-sm">RM500</button>
                </div>

                <!-- Points Preview -->
                <div class="bg-primary/10 rounded-xl p-4 mb-4 text-center" x-show="amount > 0">
                    <p class="text-sm text-surface-600">You'll earn approximately</p>
                    <p class="text-3xl font-bold text-primary" x-text="previewPoints + ' pts'"></p>
                    <p class="text-xs text-surface-500 mt-1" x-text="'100 pts = RM1.00 value'"></p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" x-model="description" class="form-input" placeholder="e.g. Lunch, Shopping...">
                </div>

                <button type="submit" class="btn-primary w-full py-4 text-lg" :disabled="loading || amount <= 0">
                    <span x-show="!loading">✨ Earn Points</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <div class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></div>
                        Processing...
                    </span>
                </button>
            </form>
        </div>
    </div>

    <!-- Points Rate Info -->
    <div class="card p-4 mt-4">
        <div class="flex items-center gap-3">
            <span class="text-2xl">💡</span>
            <div>
                <p class="font-semibold text-sm">Points Rate</p>
                <p class="text-xs text-surface-500">{{ $earnRate }} pts per RM1 spent • 100 pts = RM1.00 value</p>
            </div>
        </div>
    </div>
    @endguest
</div>

<script>
function earnApp() {
    return {
        amount: '',
        description: '',
        loading: false,
        success: false,
        error: '',
        message: '',
        pointsEarned: 0,
        newBalance: 0,
        merchantName: '{{ $merchant->company_name }}',
        redirectUrl: '',
        earnRate: {{ $earnRate }},

        init() {},

        get previewPoints() {
            const amt = parseFloat(this.amount) || 0;
            return Math.floor(amt * (100 / this.earnRate));
        },

        async earnPoints() {
            if (this.amount <= 0) return;
            this.loading = true;
            this.error = '';

            try {
                const resp = await fetch('/api/scan/earn', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        merchant_id: {{ $merchant->id }},
                        branch_id: {{ $branch?->id ?: 'null' }},
                        amount: this.amount,
                        description: this.description,
                    }),
                });

                const data = await resp.json();

                if (data.success) {
                    this.success = true;
                    this.message = data.message;
                    this.pointsEarned = data.points_earned;
                    this.newBalance = data.new_balance;
                    this.merchantName = data.merchant;
                } else {
                    this.error = data.message;
                    this.redirectUrl = data.redirect || '';
                }
            } catch(e) {
                this.error = 'Network error. Please try again.';
            }

            this.loading = false;
        },

        resetForm() {
            this.amount = '';
            this.description = '';
            this.success = false;
            this.error = '';
        }
    }
}
</script>
@endsection
