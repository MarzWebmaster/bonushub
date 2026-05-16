@extends('layouts.app')

@section('title', 'Manage Shop - BonusHub')
@section('page-title', 'Manage Shop')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Shop Management</h2>
    <p class="text-gray-600">Here you can manage your shops. You can edit, activate/deactivate, and view details of each shop.</p>

    @role('merchant')
        <div class="mt-6">
            <p class="text-sm text-gray-500">This page is available for merchant role users to manage their shop settings.</p>
            <a href="{{ route('settings') }}" class="mt-3 inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Go to Settings
            </a>
        </div>
    @else
        <div class="mt-6">
            <p class="text-sm text-gray-500">Superadmin can manage all shops from the <a href="{{ route('superadmin.merchants') }}" class="text-indigo-600 hover:text-indigo-800">Merchants</a> page.</p>
        </div>
    @endrole
</div>
@endsection
