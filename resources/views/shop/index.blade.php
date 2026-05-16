@extends('layouts.app')

@section('title', 'Shop Management - BonusHub')
@section('page-title', 'Shop Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Shop Index</h2>
    <p class="text-gray-600">Manage your shops here. You can edit, activate/deactivate, and view details of each shop.</p>

    <div class="mt-6 p-8 text-center border-2 border-dashed border-gray-300 rounded-lg">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <p class="text-gray-500">Shop management interface will be implemented here.</p>
        <p class="text-xs text-gray-400 mt-1">Manage your shops from the dedicated management pages.</p>
    </div>
</div>
@endsection
