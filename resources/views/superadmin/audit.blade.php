@extends('layouts.app')
@section('title', 'Audit Trail - BonusHub')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Audit Trail</h1>
            <p class="page-subtitle">{{ $logs->total() }} activity logs recorded</p>
        </div>
    </div>
    <div class="overflow-x-auto"><table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>Module</th>
                <th>Target</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-xs text-surface-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                <td class="text-surface-700 text-sm">{{ $log->user->name ?? 'System' }}</td>
                <td><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-bonus-100 text-bonus-700">{{ $log->action }}</span></td>
                <td class="text-surface-500 text-sm">{{ $log->module ?? '-' }}</td>
                <td class="text-surface-500 text-xs">{{ $log->target_type ?? '-' }} #{{ $log->target_id }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-surface-400 py-8">No activity logs yet.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if($logs->hasPages())
        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection