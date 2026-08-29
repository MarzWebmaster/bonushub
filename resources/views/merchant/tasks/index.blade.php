@extends('layouts.app')
@section('title', 'Viral Tasks — Merchant')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="text-2xl font-bold text-surface-800 dark:text-white">🎯 Viral Tasks</h1>
            <p class="text-surface-500 dark:text-surface-400 mt-1">Create social tasks to earn free advertising from customers</p>
        </div>
        <a href="{{ route('merchant.tasks.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Task
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase">Total Tasks</p>
            <p class="text-2xl font-bold text-surface-800 dark:text-white mt-1">{{ $stats['total_tasks'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase">Active</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active_tasks'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase">Pending Review</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_review'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase">Submissions</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['total_submissions'] }}</p>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-surface-200 dark:border-surface-700">
                        <th class="text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase px-4 py-3">Task</th>
                        <th class="text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase px-4 py-3">Platform</th>
                        <th class="text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase px-4 py-3">Points</th>
                        <th class="text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase px-4 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase px-4 py-3">Submissions</th>
                        <th class="text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="border-b border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/50">
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-medium text-surface-800 dark:text-white">{{ $task->title }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">{{ $task->task_type }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300">
                                {{ ucfirst($task->platform) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ number_format($task->points_reward) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($task->status === 'active')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                            @elseif($task->status === 'paused')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Paused</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-surface-100 text-surface-500">{{ $task->status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-surface-600 dark:text-surface-300">{{ $task->submissions_count }} <span class="text-surface-400">({{ $task->approved_submissions_count }} ✅)</span></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('merchant.tasks.detail', $task->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <p class="text-surface-500 dark:text-surface-400">No tasks yet. Create your first viral task!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($tasks, 'links'))
        <div class="px-4 py-3 border-t border-surface-200 dark:border-surface-700">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
