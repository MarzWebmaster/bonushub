@extends('layouts.app')
@section('title', 'Tasks — Earn Points')
@section('content')
<div class="page-container" style="padding-top:0" x-data="customerTasks()">
    <div class="page-header">
        <div>
            <h1 class="text-2xl font-bold text-surface-800 dark:text-white">🎯 Available Tasks</h1>
            <p class="text-surface-500 dark:text-surface-400 mt-1">Complete tasks to earn bonus points!</p>
        </div>
    </div>

    <!-- Tasks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($tasks as $task)
        <div class="card p-5 flex flex-col" :class="{{ $task->customer_submission ? 'opacity-75' : '' }}">
            <!-- Platform Badge -->
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300">
                    @if($task->platform === 'facebook')
                        <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    @elseif($task->platform === 'instagram')
                        <svg class="w-3 h-3 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    @elseif($task->platform === 'tiktok')
                        <svg class="w-3 h-3 text-surface-800" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    @elseif($task->platform === 'youtube')
                        <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    @else
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    @endif
                    {{ ucfirst($task->platform) }}
                </span>
                <span class="text-xs text-surface-500">{{ ucfirst($task->task_type) }}</span>
            </div>

            <!-- Title & Description -->
            <h3 class="font-bold text-surface-800 dark:text-white mb-1">{{ $task->title }}</h3>
            @if($task->description)
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-3 line-clamp-2">{{ $task->description }}</p>
            @endif

            <!-- Points -->
            <div class="mt-auto">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">+{{ number_format($task->points_reward) }} pts</span>
                    <span class="text-xs text-surface-500">{{ $task->submissions_count }} done</span>
                </div>

                @if($task->customer_submission)
                    <!-- Already submitted -->
                    @if($task->customer_submission->status === 'approved')
                        <div class="w-full py-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-center text-sm font-medium">
                            ✅ Approved — +{{ number_format($task->customer_submission->points_awarded) }} pts
                        </div>
                    @elseif($task->customer_submission->status === 'rejected')
                        <div class="w-full py-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-center text-sm font-medium">
                            ❌ Rejected
                            @if($task->customer_submission->review_notes)<br><span class="text-xs">{{ $task->customer_submission->review_notes }}</span>@endif
                        </div>
                    @else
                        <div class="w-full py-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-center text-sm font-medium">
                            ⏳ Pending Review
                        </div>
                    @endif
                @else
                    <!-- Submit form -->
                    <div x-show="activeTask !== {{ $task->id }}" x-cloak>
                        <button @click="activeTask = {{ $task->id }}" class="w-full py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors">
                            Do This Task →
                        </button>
                    </div>
                    <div x-show="activeTask === {{ $task->id }}" x-transition x-cloak class="space-y-3">
                        <input type="url" x-model="proofUrl" placeholder="Paste proof URL (optional)"
                            class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-sm text-surface-800 dark:text-white">
                        @if($task->requires_screenshot)
                        <div>
                            <label class="text-xs text-surface-500">📸 Screenshot proof:</label>
                            <input type="file" accept="image/*" @change="handleFile($event)" class="w-full text-sm mt-1">
                        </div>
                        @endif
                        <div class="flex gap-2">
                            <button @click="submitTask({{ $task->id }})" :disabled="submitting"
                                class="flex-1 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium disabled:opacity-50">
                                <span x-text="submitting ? 'Submitting...' : 'Submit'"></span>
                            </button>
                            <button @click="activeTask = null; proofUrl = ''; screenshotFile = null" class="px-3 py-2 rounded-lg bg-surface-200 dark:bg-surface-700 text-surface-600 dark:text-surface-300 text-sm">Cancel</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full card p-12 text-center">
            <p class="text-4xl mb-3">🎯</p>
            <p class="text-surface-500 dark:text-surface-400">No tasks available right now. Check back later!</p>
        </div>
        @endforelse
    </div>

    <!-- Feedback -->
    <div x-show="message" x-transition x-cloak
        class="fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-sm z-50"
        :class="messageType === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
        x-text="message"></div>
</div>

<script>
function customerTasks() {
    return {
        activeTask: null,
        proofUrl: '',
        screenshotFile: null,
        submitting: false,
        message: null,
        messageType: 'success',

        handleFile(event) {
            this.screenshotFile = event.target.files[0];
        },

        async submitTask(taskId) {
            this.submitting = true;
            this.message = null;

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                if (this.proofUrl) formData.append('proof_url', this.proofUrl);
                if (this.screenshotFile) formData.append('screenshot', this.screenshotFile);

                const res = await fetch(`/customer/tasks/${taskId}/submit`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await res.json();
                this.message = data.message;
                this.messageType = data.success ? 'success' : 'error';
                if (data.success) setTimeout(() => location.reload(), 1500);
            } catch (e) {
                this.message = 'Network error. Please try again.';
                this.messageType = 'error';
            }
            this.submitting = false;
            setTimeout(() => this.message = null, 4000);
        }
    };
}
</script>
@endsection
