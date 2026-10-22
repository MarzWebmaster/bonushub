@extends('layouts.app')
@section('title', 'Referral Review - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Referral Review</h1>
            <p class="page-subtitle">Signups the system flagged as unusual. Flagged signups are never rewarded until you decide.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="card overflow-hidden">
        @if($rows->isEmpty())
            <div class="text-center py-12 text-surface-400">
                <div class="text-4xl mb-2">✅</div>
                <p class="text-sm">Nothing to review. Unusual signups will appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead>
                        <tr><th>When</th><th>Referrer</th><th>New customer</th><th>Why flagged</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $r)
                            <tr>
                                <td class="text-xs text-surface-500">{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d M Y H:i') }}</td>
                                <td>{{ $r->referrer_name }}<div class="text-xs text-surface-400">{{ $r->referrer_email }}</div></td>
                                <td>{{ $r->referred_name }}<div class="text-xs text-surface-400">{{ $r->referred_email }}</div></td>
                                <td>
                                    @foreach($r->flag_labels as $label)
                                        <span class="inline-block text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full mb-1">{{ $label }}</span>
                                    @endforeach
                                </td>
                                <td style="white-space:nowrap">
                                    <form method="POST" action="{{ route('merchant.referral.review.approve', $r->id) }}" style="display:inline">
                                        @csrf
                                        <button class="btn btn-success text-xs" type="submit">✔ Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('merchant.referral.review.reject', $r->id) }}" style="display:inline" onsubmit="return confirm('Reject this referral? No bonus will be paid.')">
                                        @csrf
                                        <button class="btn btn-secondary text-xs" type="submit">✕ Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <p class="text-xs text-surface-400 mt-3">Approve = the signup continues through the normal check (the new customer must become active). Reject = it never earns a bonus.</p>
</div>
@endsection
