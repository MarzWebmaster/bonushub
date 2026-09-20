@extends('layouts.app')

@section('title', 'Settings - BonusHub')
@section('page-title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" style="padding-top:0">
    {{-- Profile Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Settings</h2>
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ Auth::user()->email }}" disabled class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 cursor-not-allowed">
                </div>
            </div>
            {{-- Merchant-specific settings --}}
            @role('merchant')
                <hr class="border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Shop Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shop Name</label>
                        <input type="text" name="shop_name" value="{{ old('shop_name', $shopName ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Point Rate (per RM)</label>
                        <input type="number" step="0.01" name="point_rate" value="{{ old('point_rate', $pointRate ?? '1.00') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            @endrole
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Save Settings</button>
            </div>
        </form>
    </div>

    {{-- Notification Preferences --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h2>
        <form method="POST" action="{{ route('settings.notifications') }}">
            @csrf
            <div class="space-y-3">
                <label class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Email Notifications</p>
                        <p class="text-xs text-gray-500">Receive activity updates via email</p>
                    </div>
                    <input type="checkbox" name="email_notifications" value="1" {{ ($emailNotifications ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </label>
                <label class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Redemption Requests</p>
                        <p class="text-xs text-gray-500">Get notified when customers redeem points</p>
                    </div>
                    <input type="checkbox" name="redemption_notifications" value="1" {{ ($redemptionNotifications ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </label>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Save Preferences</button>
            </div>
        </form>
    </div>

    {{-- Password Change --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>
        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Update Password</button>
            </div>
        </form>
    </div>

    {{-- Stripe Payment Settings (Superadmin only) --}}
    @role('superadmin')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Stripe Payment Gateway</h2>
                <p class="text-xs text-gray-500">Configure Stripe to accept merchant subscription payments</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.stripe') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Publishable Key</label>
                    <input type="text" name="stripe_key" value="{{ config('services.stripe.key') }}" placeholder="pk_live_..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                    <input type="password" name="stripe_secret" value="{{ config('services.stripe.secret') }}" placeholder="sk_live_..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Signing Secret</label>
                    <input type="password" name="stripe_webhook_secret" value="{{ config('services.stripe.webhook_secret') }}" placeholder="whsec_..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Set webhook URL in Stripe dashboard: <code class="bg-gray-100 px-1 rounded">{{ url('/stripe/webhook') }}</code></p>
                </div>
            </div>

            {{-- Status indicator --}}
            <div class="flex items-center gap-2 p-3 rounded-lg {{ config('services.stripe.secret') ? 'bg-green-50 border border-green-200' : 'bg-amber-50 border border-amber-200' }}">
                @if(config('services.stripe.secret'))
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-sm text-green-700 font-medium">Stripe is configured</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-sm text-amber-700 font-medium">Stripe not configured — add your API keys</span>
                @endif
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">Save Stripe Settings</button>
            </div>
        </form>
    </div>
    @endrole
</div>
@endsection