@extends('layouts.app')
@section('title', 'Payment History')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">💳 Payment History</h1>
            <p class="page-subtitle">Your subscription and payment records</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="text-sm text-surface-500">{{ $payment->created_at->format('d M Y H:i') }}</td>
                        <td class="font-medium">{{ $payment->package->name ?? 'N/A' }}</td>
                        <td class="font-bold text-bonus-600">RM{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            @if($payment->status === 'succeeded')
                                <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Paid</span>
                            @elseif($payment->status === 'pending')
                                <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">Pending</span>
                            @else
                                <span class="text-xs bg-red-50 text-red-700 px-2 py-0.5 rounded-full">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-surface-400">#{{ $payment->id }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-surface-400 py-8">No payments yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-4 py-3 border-t border-surface-200">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection