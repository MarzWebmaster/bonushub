@extends('layouts.app')

@section('title', 'Manage Shop Package - BonusHub')
@section('page-title', 'Manage Packages')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Package Management</h2>
    <p class="text-gray-600">Define and manage different shop packages (e.g., Basic, Premium, Enterprise) with varying features, limits, and pricing.</p>

    <div class="mt-6">
        <p class="text-sm text-gray-500">Manage packages from the superadmin <a href="{{ route('superadmin.packages') }}" class="text-indigo-600 hover:text-indigo-800">Packages</a> page.</p>
    </div>
</div>
@endsection
