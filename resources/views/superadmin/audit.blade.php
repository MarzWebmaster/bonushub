@extends('layouts.app')
@section('title', 'Audit Trail - BonusHub')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Audit Trail</h1>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Target</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $log->created_at }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->user_id ?? 'System' }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $log->action }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->target_table }} #{{ $log->target_id }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection