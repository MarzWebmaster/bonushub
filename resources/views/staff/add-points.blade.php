@extends('layouts.app')

@section('title', 'Add Points - BonusHub')
@section('page-title', 'Add Points to Customer')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Award Points</h2>

        <form method="POST" action="{{ route('staff.add-points.submit') }}" class="space-y-4">
            @csrf

            {{-- Customer Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                @if(isset($customer) && $customer)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold uppercase">
                            {{ substr($customer->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $customer->email }}</p>
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

            {{-- Points Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Points to Award</label>
                <div class="relative">
                    <input type="number" name="points" value="{{ old('points', request('points')) }}" required min="1" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-2xl font-bold text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0">
                </div>
                @error('points')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Receipt / Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt / Reference (optional)</label>
                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g., INV-00123" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Reason for awarding points...">{{ old('notes') }}</textarea>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-gray-500">This action may require merchant approval.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Award Points
                </button>
            </div>
        </form>
    </div>

    {{-- Quick Presets --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Quick Select Amount</h3>
        <div class="flex flex-wrap gap-2">
            @foreach([10, 25, 50, 100, 200, 500] as $amount)
                <button type="button" onclick="document.querySelector('[name=points]').value = {{ $amount }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">{{ $amount }}</button>
            @endforeach
        </div>
    </div>

    {{-- Recent Awards --}}
    @if(isset($recentAwards) && count($recentAwards) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Recent Awards</h3>
            <div class="divide-y divide-gray-100">
                @foreach($recentAwards as $award)
                    <div class="py-2 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">{{ $award->customer_name ?? $award->customer->name ?? 'Customer' }}</p>
                            <p class="text-xs text-gray-400">{{ $award->created_at ? $award->created_at->diffForHumans() : '' }}</p>
                        </div>
                        <span class="text-sm font-bold text-green-600">+{{ number_format($award->points ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
