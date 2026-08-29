@extends('layouts.app')
@section('title', 'Create Viral Task — Merchant')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <a href="{{ route('merchant.tasks') }}" class="text-surface-500 hover:text-surface-700 flex items-center gap-1 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Tasks
        </a>
    </div>

    <div class="card p-6 max-w-2xl mx-auto" x-data="taskForm()">
        <h2 class="text-xl font-bold text-surface-800 dark:text-white mb-6">🎯 Create Viral Task</h2>

        <form @submit.prevent="submitTask()">
            {{-- Title --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Task Title *</label>
                <input type="text" x-model="form.title" required
                    class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g., Share our new menu on Facebook">
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Description</label>
                <textarea x-model="form.description" rows="3"
                    class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    placeholder="Describe what the customer needs to do..."></textarea>
            </div>

            {{-- Platform + Type --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Platform *</label>
                    <select x-model="form.platform"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white">
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">TikTok</option>
                        <option value="twitter">Twitter / X</option>
                        <option value="youtube">YouTube</option>
                        <option value="any">Any Platform</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Task Type *</label>
                    <select x-model="form.task_type"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white">
                        <option value="share_post">Share Post</option>
                        <option value="follow">Follow Account</option>
                        <option value="refer_friend">Refer a Friend</option>
                        <option value="visit_link">Visit Link</option>
                        <option value="custom">Custom Task</option>
                    </select>
                </div>
            </div>

            {{-- Points + Completions --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Points Reward *</label>
                    <input type="number" x-model.number="form.points_reward" required min="1" max="100000"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        placeholder="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Max Completions</label>
                    <input type="number" x-model.number="form.max_completions" min="1"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        placeholder="Unlimited">
                </div>
            </div>

            {{-- Screenshot Required --}}
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.requires_screenshot"
                        class="w-4 h-4 text-indigo-600 border-surface-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-surface-700 dark:text-surface-300">Require screenshot proof from customer</span>
                </label>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Start Date</label>
                    <input type="datetime-local" x-model="form.starts_at"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">End Date</label>
                    <input type="datetime-local" x-model="form.ends_at"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-white dark:bg-surface-800 text-surface-800 dark:text-white">
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('merchant.tasks') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <template x-if="submitting">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" class="opacity-75"/></svg>
                    </template>
                    <span x-text="submitting ? 'Creating...' : 'Create Task'"></span>
                </button>
            </div>
        </form>

        {{-- Success/Error --}}
        <div x-show="success" x-transition class="mt-4 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm" x-cloak>
            Task created! <a href="{{ route('merchant.tasks') }}" class="underline">View all tasks →</a>
        </div>
        <div x-show="error" x-transition class="mt-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm" x-cloak x-text="error"></div>
    </div>
</div>

<script>
function taskForm() {
    return {
        form: {
            title: '',
            description: '',
            platform: 'facebook',
            task_type: 'share_post',
            points_reward: 100,
            requires_screenshot: true,
            max_completions: null,
            starts_at: '',
            ends_at: '',
        },
        submitting: false,
        success: false,
        error: null,

        async submitTask() {
            this.submitting = true;
            this.error = null;
            this.success = false;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('/merchant/api/tasks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (data.success) {
                    this.success = true;
                } else {
                    this.error = data.message || 'Failed to create task.';
                }
            } catch (e) {
                this.error = 'Network error. Please try again.';
            }
            this.submitting = false;
        }
    };
}
</script>
@endsection
