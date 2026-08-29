@extends('layouts.app')
@section('title', 'QR Code - Staff')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">📱 QR Code</h1>
            <p class="page-subtitle">Show this QR code to your customers to earn points</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- QR Code Display -->
        <div class="card p-6 text-center" x-data="qrApp()" x-init="generateQr()">
            <h2 class="font-bold text-surface-800 mb-4">Scan QR Code</h2>

            <!-- Branch Selector -->
            <div class="mb-4">
                <label class="form-label">Branch (optional)</label>
                <select x-model="branchId" @change="generateQr()" class="form-input">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- QR Code Image -->
            <div class="mb-4 p-4 bg-white rounded-xl border-2 border-surface-200 inline-block" x-show="qrImage">
                <img :src="qrImage" alt="QR Code" class="mx-auto" style="max-width:280px">
            </div>

            <!-- Loading -->
            <div x-show="loading" class="text-surface-500 py-8">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full"></div>
                <p class="mt-2">Generating QR code...</p>
            </div>

            <!-- Merchant Info -->
            <div class="text-sm text-surface-600 mt-4">
                <p class="font-semibold" x-text="merchantName"></p>
                <p x-text="branchName"></p>
            </div>

            <!-- Scan URL -->
            <div class="mt-4 p-3 bg-surface-50 rounded-lg text-sm text-surface-600 break-all" x-show="scanUrl">
                <span class="font-mono" x-text="scanUrl"></span>
            </div>

            <!-- Print Button -->
            <button @click="printQr()" class="btn-primary mt-4" x-show="qrImage">
                🖨️ Print QR Code
            </button>
        </div>

        <!-- Instructions -->
        <div class="card p-6">
            <h2 class="font-bold text-surface-800 mb-4">How It Works</h2>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">1️⃣</span>
                    <div>
                        <h3 class="font-semibold">Show QR to Customer</h3>
                        <p class="text-sm text-surface-600">Customer scans this QR code with their phone camera</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-2xl">2️⃣</span>
                    <div>
                        <h3 class="font-semibold">Customer Enters Amount</h3>
                        <p class="text-sm text-surface-600">They enter how much they spent at your store</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-2xl">3️⃣</span>
                    <div>
                        <h3 class="font-semibold">Points Auto-Credit</h3>
                        <p class="text-sm text-surface-600">Points are calculated and added to their wallet instantly</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-4 bg-primary/10 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">💡 Points Rate</h3>
                @php
                    $loyaltyRate = \App\Models\LoyaltyRate::where('merchant_id', $merchant->id)->first();
                    $rate = $loyaltyRate ? $loyaltyRate->earn_rate : 100;
                @endphp
                <p class="text-sm text-surface-700">
                    Earn <strong>{{ $rate }} points</strong> per RM1 spent.
                </p>
                <p class="text-sm text-surface-600 mt-1">
                    Example: RM50 spent = {{ 50 * (100 / $rate) }} points
                </p>
            </div>

            <div class="mt-4 p-4 bg-secondary/10 rounded-lg">
                <h3 class="font-semibold text-secondary mb-2">🔒 Security</h3>
                <ul class="text-sm text-surface-600 space-y-1">
                    <li>• QR code is unique to your merchant</li>
                    <li>• Customer must be logged in to earn points</li>
                    <li>• Customer must have joined your merchant</li>
                    <li>• Transaction is logged for audit trail</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function qrApp() {
    return {
        qrImage: '',
        scanUrl: '',
        merchantName: '{{ $merchant->company_name }}',
        branchName: '{{ $branches->isNotEmpty() ? "Select a branch above" : "No branches configured" }}',
        branchId: '',
        loading: true,

        async generateQr() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.branchId) params.append('branch_id', this.branchId);
                const resp = await fetch('{{ route("staff.api.qr") }}?' + params.toString());
                const data = await resp.json();
                if (data.success) {
                    this.qrImage = data.qr_image;
                    this.scanUrl = data.url;
                    this.merchantName = data.merchant;
                    this.branchName = data.branch;
                }
            } catch(e) {
                console.error('QR generation failed:', e);
            }
            this.loading = false;
        },

        printQr() {
            const win = window.open('', '_blank');
            win.document.write(`
                <html><head><title>QR Code - ${this.merchantName}</title>
                <style>body{text-align:center;padding:40px;font-family:Arial}h1{font-size:24px}img{max-width:300px}</style>
                </head><body>
                <h1>${this.merchantName}</h1>
                <p>${this.branchName}</p>
                <img src="${this.qrImage}" alt="QR Code">
                <p style="font-size:12px;color:#666;margin-top:20px">Scan to earn loyalty points</p>
                </body></html>
            `);
            win.document.close();
            win.print();
        }
    }
}
</script>
@endsection
