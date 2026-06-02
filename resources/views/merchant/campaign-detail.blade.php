@extends('layouts.app')
@section('title', $campaign->name . ' - Campaign Detail')
@section('content')
<div class="page-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('merchant.campaigns') }}" class="text-surface-400 hover:text-bonus-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="page-title">{{ $campaign->name }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $campaign->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface-100 text-surface-500' }}">
                    {{ $campaign->status }}
                </span>
            </div>
            <p class="page-subtitle">
                {{ $campaign->medium ? ucfirst($campaign->medium) : 'No source' }}
                • Created {{ $campaign->created_at->diffForHumans() }}
                @if($campaign->expires_at)
                    • Expires {{ $campaign->expires_at->format('d M Y H:i') }}
                @endif
            </p>
        </div>
        <button onclick="showQR()" class="btn-primary">📱 Show QR Code</button>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stat-card border-l-bonus-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Total Visits</p>
            <p class="text-3xl font-bold text-surface-800 dark:text-white mt-1">{{ number_format($campaign->visits) }}</p>
        </div>
        <div class="stat-card border-l-emerald-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Registrations</p>
            <p class="text-3xl font-bold text-surface-800 dark:text-white mt-1">{{ number_format($campaign->registrations) }}</p>
        </div>
        <div class="stat-card border-l-purple-500">
            <p class="text-xs text-surface-500 uppercase tracking-wider">Conversion Rate</p>
            <p class="text-3xl font-bold text-surface-800 dark:text-white mt-1">{{ $campaign->conversion_rate }}%</p>
        </div>
    </div>

    {{-- Link Box --}}
    <div class="card p-4 mb-6">
        <p class="text-sm font-medium text-surface-600 dark:text-surface-300 mb-2">🔗 Campaign Link</p>
        <div class="flex items-center gap-2">
            <input value="{{ $campaign->url }}" class="form-input flex-1 font-mono text-sm" readonly id="campaign-url">
            <button onclick="copyUrl()" class="btn-primary text-sm px-4 py-2" id="copy-btn">📋 Copy</button>
        </div>
    </div>

    {{-- Registered Customers Table --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-bold text-surface-800 dark:text-white">Customers Registered via This Link</h2>
            <span class="text-sm text-surface-400">{{ $customers->total() }} total</span>
        </div>
        <div class="card-body">
            @if($customers->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-surface-100 dark:border-surface-700">
                            <th class="text-left py-3 px-4 text-surface-500 font-medium">Customer</th>
                            <th class="text-left py-3 px-4 text-surface-500 font-medium">Phone</th>
                            <th class="text-left py-3 px-4 text-surface-500 font-medium">Tier</th>
                            <th class="text-left py-3 px-4 text-surface-500 font-medium">Points</th>
                            <th class="text-left py-3 px-4 text-surface-500 font-medium">Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $cm)
                        <tr class="border-b border-surface-50 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/50">
                            <td class="py-3 px-4 font-medium">{{ $cm->customer->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-surface-500">{{ $cm->customer->phone ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($cm->tier_per_merchant === 'Platinum') bg-purple-100 text-purple-700
                                    @elseif($cm->tier_per_merchant === 'Gold') bg-amber-100 text-amber-700
                                    @elseif($cm->tier_per_merchant === 'Silver') bg-surface-200 text-surface-600
                                    @else bg-surface-100 text-surface-500 @endif">
                                    {{ $cm->tier_per_merchant }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-bonus-600">{{ number_format($cm->points) }}</td>
                            <td class="py-3 px-4 text-surface-400">{{ $cm->tied_at ? \Carbon\Carbon::parse($cm->tied_at)->format('d M Y') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $customers->links() }}</div>
            @else
            <div class="text-center py-8">
                <p class="text-surface-400">No customers registered via this link yet.</p>
                <p class="text-surface-300 text-sm mt-1">Share the link or QR code to start getting registrations!</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- QR Modal --}}
<div id="qr-modal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal-content max-w-xs text-center">
        <div class="modal-header">
            <h2 class="text-lg font-bold">{{ $campaign->name }}</h2>
            <button onclick="this.closest('.modal-overlay').classList.add('hidden')">&times;</button>
        </div>
        <div class="modal-body">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($campaign->url) }}" alt="QR Code" class="mx-auto rounded-xl shadow-lg mb-4">
            <p class="text-sm text-surface-500">Scan to register</p>
            <p class="text-xs text-surface-400 font-mono mt-1">{{ $campaign->url }}</p>
        </div>
    </div>
</div>

<script>
function copyUrl() {
    const input = document.getElementById('campaign-url');
    input.select();
    navigator.clipboard.writeText(input.value);
    const btn = document.getElementById('copy-btn');
    btn.textContent = '✅ Copied!';
    setTimeout(() => btn.textContent = '📋 Copy', 1500);
}

function showQR() {
    document.getElementById('qr-modal').classList.remove('hidden');
}
</script>
@endsection
