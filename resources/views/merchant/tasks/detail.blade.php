@extends('layouts.app')
@section('title', $task->title . ' — Viral Task')
@section('content')
<div class="page-container" style="padding-top:0" x-data="taskDetail({{ $task->id }})">
    <div class="page-header">
        <a href="{{ route('merchant.tasks') }}" class="text-surface-500 hover:text-surface-700 flex items-center gap-1 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Tasks
        </a>
    </div>

    <!-- Task Info -->
    <div class="card p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-surface-800 dark:text-white">{{ $task->title }}</h2>
                @if($task->description)
                    <p class="text-surface-500 dark:text-surface-400 mt-1">{{ $task->description }}</p>
                @endif
                <div class="flex items-center gap-3 mt-3 text-sm text-surface-500">
                    <span class="px-2 py-1 rounded bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300">{{ ucfirst($task->platform) }}</span>
                    <span>{{ ucfirst($task->task_type) }}</span>
                    <span class="font-semibold text-indigo-600">{{ number_format($task->points_reward) }} pts</span>
                    @if($task->requires_screenshot)<span>📸 Screenshot required</span>@endif
                    @if($task->ends_at)<span>Ends: {{ $task->ends_at->format('d M Y') }}</span>@endif
                </div>
            </div>
            <div class="flex gap-2">
                @if($task->status === 'active')
                    <button @click="toggleTask('paused')" class="btn btn-secondary text-sm">⏸ Pause</button>
                @elseif($task->status === 'paused')
                    <button @click="toggleTask('active')" class="btn btn-primary text-sm">▶ Resume</button>
                @endif
            </div>
        </div>

        <!-- Progress -->
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-surface-200 dark:border-surface-700">
            <div>
                <p class="text-xs text-surface-500">Completions</p>
                <p class="text-lg font-bold text-surface-800 dark:text-white">{{ $task->current_completions }}{{ $task->max_completions ? ' / '.$task->max_completions : '' }}</p>
            </div>
            <div>
                <p class="text-xs text-surface-500">Points Spent</p>
                <p class="text-lg font-bold text-amber-600">{{ number_format($task->total_points_spent) }}</p>
            </div>
            <div>
                <p class="text-xs text-surface-500">Status</p>
                <p class="text-lg font-bold">{{ ucfirst($task->status) }}</p>
            </div>
        </div>
    </div>

    <!-- Submissions -->
    <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-surface-200 dark:border-surface-700">
            <h3 class="font-semibold text-surface-800 dark:text-white">Submissions ({{ $submissions->total() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-surface-200 dark:border-surface-700">
                        <th class="text-left text-xs font-medium text-surface-500 uppercase px-4 py-3">Customer</th>
                        <th class="text-left text-xs font-medium text-surface-500 uppercase px-4 py-3">Proof</th>
                        <th class="text-left text-xs font-medium text-surface-500 uppercase px-4 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-surface-500 uppercase px-4 py-3">Submitted</th>
                        <th class="text-left text-xs font-medium text-surface-500 uppercase px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $sub)
                    <tr class="border-b border-surface-100 dark:border-surface-800">
                        <td class="px-4 py-3">
                            <p class="font-medium text-surface-800 dark:text-white text-sm">{{ $sub->customer->name ?? 'N/A' }}</p>
                            <p class="text-xs text-surface-500">{{ $sub->customer->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($sub->proof_url)
                                <a href="{{ $sub->proof_url }}" target="_blank" class="text-indigo-600 hover:underline text-sm truncate block max-w-[200px]">{{ $sub->proof_url }}</a>
                            @endif
                            @if($sub->screenshot_path)
                                <a href="{{ Storage::url($sub->screenshot_path) }}" target="_blank" class="text-xs text-surface-500 hover:text-indigo-600">📸 View screenshot</a>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($sub->status === 'pending')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                            @elseif($sub->status === 'approved')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Approved</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-surface-500">{{ $sub->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            @if($sub->status === 'pending')
                            <div class="flex items-center gap-2">
                                <button @click="review({{ $sub->id }}, 'approve')" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">✅ Approve</button>
                                <button @click="review({{ $sub->id }}, 'reject')" class="text-red-600 hover:text-red-800 text-sm font-medium">❌ Reject</button>
                            </div>
                            @else
                            <span class="text-xs text-surface-500">Reviewed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-surface-500">No submissions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($submissions, 'links'))
        <div class="px-4 py-3 border-t border-surface-200 dark:border-surface-700">
            {{ $submissions->links() }}
        </div>
        @endif
    </div>

    <!-- Review Feedback -->
    <div x-show="message" x-transition x-cloak
        class="fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-sm z-50"
        :class="messageType === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
        x-text="message"></div>
</div>

<script>
function taskDetail(taskId) {
    return {
        message: null,
        messageType: 'success',

        async review(submissionId, action) {
            const notes = action === 'reject' ? prompt('Rejection reason (optional):') : null;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch(`/merchant/api/submissions/${submissionId}/review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action, review_notes: notes }),
                });
                const data = await res.json();
                this.message = data.message;
                this.messageType = data.success ? 'success' : 'error';
                if (data.success) setTimeout(() => location.reload(), 1000);
            } catch (e) {
                this.message = 'Network error.';
                this.messageType = 'error';
            }
            setTimeout(() => this.message = null, 3000);
        },

        async toggleTask(status) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch(`/merchant/api/tasks/${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                });
                const data = await res.json();
                if (data.success) location.reload();
            } catch (e) {}
        }
    };
}
</script>
@endsection
