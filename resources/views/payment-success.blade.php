@extends('layouts.app')
@section('title', 'Payment Successful')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="max-w-md mx-auto text-center py-12">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-2xl font-bold text-green-600 mb-2">Payment Successful!</h1>
        <p class="text-surface-500 mb-6">Thank you for your payment. Your package has been activated.</p>

        @if($payment)
        <div class="card p-5 text-left mb-6">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-surface-500">Package</span><span class="font-medium">{{ $payment->package->name ?? 'N/A' }}</span></div>
                <div class="flex justify-between"><span class="text-surface-500">Amount</span><span class="font-bold text-bonus-600">RM{{ number_format($payment->amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-surface-500">Status</span><span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Paid</span></div>
                <div class="flex justify-between"><span class="text-surface-500">Reference</span><span class="text-xs text-surface-400">#{{ $payment->id }}</span></div>
            </div>
        </div>
        @endif

        <a href="{{ route('merchant.dashboard') }}" class="btn-primary px-6 py-2.5">Go to Dashboard</a>
    </div>
</div>
@endsection