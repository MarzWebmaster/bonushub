@extends('layouts.app')

@section('title', 'Redeem Points - BonusHub')
@section('page-title', 'Customer Redemption')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Step 1: Select Customer --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Process Redemption</h2>

        <form method="POST" action="{{ route('staff.redeem.submit') }}" class="space-y-4">
            @csrf

            {{-- Customer --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                @if(isset($customer) && $customer)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold uppercase">
                            {{ substr($customer->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-indigo-600">{{ number_format($customer->points_balance ?? $customer->current_points ?? 0) }}</p>
                            <p class="text-xs text-gray-500">points available</p>
                        </div>
                    </div>
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                @else
                    <select name="customer_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a customer...</option>
                        @foreach($customers ?? [] as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Reward Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Reward</label>
                @if(isset($rewards) && count($rewards) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($rewards as $reward)
                            <label class="relative block p-4 border rounded-lg cursor-pointer transition hover:border-indigo-300 hover:bg-indigo-50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:ring-2 has-[:checked]:ring-indigo-500">
                                <input type="radio" name="reward_id" value="{{ $reward->id }}" class="absolute opacity-0" onchange="updateSelectedReward(this)" data-points="{{ $reward->points_required }}" data-name="{{ $reward->name }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $reward->name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $reward->description ?? 'No description' }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-indigo-600">{{ number_format($reward->points_required) }} pts</span>
                                </div>
                                @if(($reward->stock ?? 1) <= 0)
                                    <p class="text-xs text-red-500 mt-2">Out of stock</p>
                                @endif
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No rewards available for redemption.</p>
                @endif
            </div>

            {{-- Selected Reward Info --}}
            <div id="reward-summary" class="hidden bg-amber-50 rounded-lg p-4 border border-amber-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700">Selected: <strong id="selected-reward-name">-</strong></p>
                        <p class="text-xs text-gray-500" id="selected-reward-points">0 points required</p>
                    </div>
                    <p class="text-xs text-gray-500">Customer balance: <span id="customer-balance-display">{{ number_format(($customer->points_balance ?? $customer->current_points ?? 0)) }}</span></p>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Any notes about this redemption...">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                Process Redemption
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function updateSelectedReward(radio) {
    const summary = document.getElementById('reward-summary');
    const name = document.getElementById('selected-reward-name');
    const pts = document.getElementById('selected-reward-points');
    if (radio.checked) {
        summary.classList.remove('hidden');
        name.textContent = radio.dataset.name;
        pts.textContent = Number(radio.dataset.points).toLocaleString() + ' points required';
    }
}
</script>
@endpush
@endsection
