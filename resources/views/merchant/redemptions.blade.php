@extends('layouts.app')
@section('title', 'Redemptions - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Redemptions</h1>
            <p class="page-subtitle">Rewards your customers redeemed. Hand them over (or cancel and refund the points).</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="card p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1"><label class="form-label">Search (claim code, name or phone)</label>
                <input name="q" value="{{ $q }}" class="form-input" placeholder="e.g. K7P2QX9A"></div>
            <div><label class="form-label">Show</label>
                <select name="status" class="form-input">
                    <option value="" @selected(!$status)>Waiting to hand over</option>
                    @foreach(['completed' => 'Handed over', 'cancelled' => 'Cancelled', 'pending' => 'Pending only', 'approved' => 'Approved only'] as $v => $l)
                        <option value="{{ $v }}" @selected($status === $v)>{{ $l }}</option>
                    @endforeach
                </select></div>
            <button type="submit" class="btn-primary px-5 py-2">Filter</button>
        </form>
    </div>

    <div class="card p-0 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-surface-500">
                <tr><th class="px-4 py-3">Claim code</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Reward</th><th class="px-4 py-3 text-right">Points</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Date</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($rows as $r)
                <tr>
                    <td class="px-4 py-3 font-mono font-semibold">{{ $r->claim_code }}</td>
                    <td class="px-4 py-3">{{ $r->customer }}<div class="text-xs text-surface-400">{{ $r->phone }}</div></td>
                    <td class="px-4 py-3">{{ $r->reward }}<div class="text-xs text-surface-400">{{ str_replace('_', ' ', $r->claim_method) }}</div></td>
                    <td class="px-4 py-3 text-right">{{ number_format($r->points_used) }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $r->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($r->status === 'cancelled' ? 'bg-gray-100 text-gray-600' : 'bg-amber-50 text-amber-700') }}">{{ $r->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-500">{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if(in_array($r->status, ['pending', 'approved'], true))
                            <form method="POST" action="{{ route('merchant.redemptions.complete', $r->id) }}" class="inline">@csrf
                                <button class="btn-primary px-3 py-1 text-xs">Handed over</button></form>
                            <form method="POST" action="{{ route('merchant.redemptions.cancel', $r->id) }}" class="inline" onsubmit="return confirm('Cancel this redemption and refund {{ (int) $r->points_used }} points to the customer?')">@csrf
                                <button class="btn-danger px-3 py-1 text-xs">Cancel + refund</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-surface-500">Nothing here.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
