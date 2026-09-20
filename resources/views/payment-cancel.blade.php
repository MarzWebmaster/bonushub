@extends('layouts.app')
@section('title', 'Payment Cancelled')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="max-w-md mx-auto text-center py-12">
        <div class="text-6xl mb-4">❌</div>
        <h1 class="text-2xl font-bold text-surface-700 mb-2">Payment Cancelled</h1>
        <p class="text-surface-500 mb-6">Your payment was not completed. No charges were made.</p>
        <div class="flex gap-3 justify-center">
            <a href="{{ route('superadmin.packages') }}" class="btn-primary px-6 py-2.5">Try Again</a>
            <a href="{{ route('merchant.dashboard') }}" class="btn-secondary px-6 py-2.5">Dashboard</a>
        </div>
    </div>
</div>
@endsection