@extends('layouts.app')

@section('title', 'Settings - BonusHub')
@section('page-title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
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
</div>
@endsection
