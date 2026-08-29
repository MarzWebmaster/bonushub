<?php

namespace App\Http\Controllers;

use App\Models\ViralTask;
use App\Models\TaskSubmission;
use App\Models\Customer;
use App\Models\PointsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ViralTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function getMerchantId(): int
    {
        return Auth::user()->merchant_id;
    }

    // ========================
    // MERCHANT — Task Management
    // ========================

    public function merchantTasksPage(): View
    {
        $merchantId = $this->getMerchantId();
        $tasks = ViralTask::forMerchant($merchantId)
            ->withCount(['submissions', 'approvedSubmissions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_tasks' => ViralTask::forMerchant($merchantId)->count(),
            'active_tasks' => ViralTask::forMerchant($merchantId)->where('status', 'active')->count(),
            'pending_review' => TaskSubmission::forMerchant($merchantId)->pending()->count(),
            'total_submissions' => TaskSubmission::forMerchant($merchantId)->count(),
        ];

        return view('merchant.tasks.index', compact('tasks', 'stats'));
    }

    public function merchantTaskCreatePage(): View
    {
        return view('merchant.tasks.create');
    }

    public function merchantTaskDetailPage(int $id): View
    {
        $merchantId = $this->getMerchantId();
        $task = ViralTask::forMerchant($merchantId)->findOrFail($id);
        $submissions = TaskSubmission::where('task_id', $id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('merchant.tasks.detail', compact('task', 'submissions'));
    }

    public function storeTask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'platform' => 'required|in:facebook,instagram,tiktok,twitter,youtube,any',
            'task_type' => 'required|in:share_post,follow,refer_friend,visit_link,custom',
            'points_reward' => 'required|integer|min:1|max:100000',
            'requires_screenshot' => 'boolean',
            'max_completions' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $task = ViralTask::create([
            'merchant_id' => $this->getMerchantId(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'platform' => $validated['platform'],
            'task_type' => $validated['task_type'],
            'points_reward' => $validated['points_reward'],
            'requires_screenshot' => $validated['requires_screenshot'] ?? true,
            'max_completions' => $validated['max_completions'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Viral task created!',
        ], 201);
    }

    public function updateTask(Request $request, int $id): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $task = ViralTask::forMerchant($merchantId)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'platform' => 'sometimes|in:facebook,instagram,tiktok,twitter,youtube,any',
            'task_type' => 'sometimes|in:share_post,follow,refer_friend,visit_link,custom',
            'points_reward' => 'sometimes|integer|min:1|max:100000',
            'requires_screenshot' => 'boolean',
            'max_completions' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:active,paused,completed',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Task updated!',
        ]);
    }

    public function deleteTask(int $id): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $task = ViralTask::forMerchant($merchantId)->findOrFail($id);
        $task->delete();

        return response()->json(['success' => true, 'message' => 'Task deleted.']);
    }

    // ========================
    // MERCHANT — Submission Review
    // ========================

    public function reviewSubmission(Request $request, int $submissionId): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'nullable|string|max:500',
        ]);

        $submission = TaskSubmission::whereHas('task', function ($q) {
            $q->where('merchant_id', $this->getMerchantId());
        })->findOrFail($submissionId);

        if ($submission->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already reviewed.'], 422);
        }

        $submission->update([
            'status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        // If approved, credit points
        if ($validated['action'] === 'approve') {
            $task = $submission->task;
            $submission->update(['points_awarded' => $task->points_reward]);

            // Update task counters
            $task->increment('current_completions');
            $task->increment('total_points_spent', $task->points_reward);

            // Credit customer points
            $customer = $submission->customer;
            $merchantId = $this->getMerchantId();
            $existingBalance = \App\Models\CustomerMerchant::where('customer_id', $customer->id)
                ->where('merchant_id', $merchantId)
                ->first();

            if ($existingBalance) {
                $existingBalance->increment('points', $task->points_reward);
            } else {
                \App\Models\CustomerMerchant::create([
                    'customer_id' => $customer->id,
                    'merchant_id' => $merchantId,
                    'points' => $task->points_reward,
                    'tier_per_merchant' => 'Basic',
                ]);
            }

            // Log transaction (uses 'notes' column, no reference_type/ID)
            PointsTransaction::create([
                'customer_id' => $customer->id,
                'merchant_id' => $merchantId,
                'type' => 'earn',
                'points' => $task->points_reward,
                'notes' => "Viral task: {$task->title} (submission #{$submission->id})",
                'staff_id' => Auth::id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'submission' => $submission->fresh(['customer']),
            'message' => $validated['action'] === 'approve' ? 'Approved + points credited!' : 'Submission rejected.',
        ]);
    }

    // ========================
    // CUSTOMER — Browse & Submit Tasks
    // ========================

    public function customerTasksPage(): View
    {
        $user = Auth::user();
        $customer = $user->customer;

        // Customers see ALL active tasks from any merchant
        $tasks = ViralTask::active()
            ->available()
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add submission status for each task
        $tasks->each(function ($task) use ($customer) {
            $task->customer_submission = $customer
                ? $task->getCustomerSubmission($customer->id)
                : null;
        });

        return view('customer.tasks', compact('tasks', 'customer'));
    }

    public function submitTask(Request $request, int $taskId): JsonResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.'], 422);
        }

        $task = ViralTask::active()->available()->findOrFail($taskId);

        if (!$task->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'Task is no longer available.'], 422);
        }

        if ($task->hasCustomerSubmitted($customer->id)) {
            return response()->json(['success' => false, 'message' => 'You already submitted for this task.'], 422);
        }

        $validated = $request->validate([
            'proof_url' => 'nullable|url|max:500',
            'screenshot' => 'nullable|image|max:5120', // 5MB max
        ]);

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('task-submissions', 'public');
        }

        $submission = TaskSubmission::create([
            'task_id' => $taskId,
            'customer_id' => $customer->id,
            'proof_url' => $validated['proof_url'] ?? null,
            'screenshot_path' => $screenshotPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'submission' => $submission,
            'message' => 'Submission sent for review!',
        ], 201);
    }

    // ========================
    // MERCHANT — Task Analytics
    // ========================

    public function taskAnalyticsPage(): View
    {
        return view('merchant.task-analytics');
    }

    public function taskAnalytics(): JsonResponse
    {
        $merchantId = $this->getMerchantId();
        $tasks = ViralTask::forMerchant($merchantId)->get();

        $totalSubmissions = TaskSubmission::forMerchant($merchantId)->count();
        $approvedSubmissions = TaskSubmission::forMerchant($merchantId)->approved()->count();
        $totalPointsSpent = $tasks->sum('total_points_spent');
        $conversionRate = $totalSubmissions > 0 ? round(($approvedSubmissions / $totalSubmissions) * 100, 1) : 0;

        $topTasks = ViralTask::forMerchant($merchantId)
            ->withCount('approvedSubmissions')
            ->orderByDesc('approved_submissions_count')
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'completions' => $t->current_completions,
                'points_spent' => $t->total_points_spent,
            ]);

        // Submissions by day (last 30 days)
        $dailyData = TaskSubmission::forMerchant($merchantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as day, status, COUNT(*) as count')
            ->groupBy('day', 'status')
            ->get()
            ->groupBy('day')
            ->map(fn($rows, $day) => [
                'date' => $day,
                'pending' => $rows->where('status', 'pending')->sum('count'),
                'approved' => $rows->where('status', 'approved')->sum('count'),
                'rejected' => $rows->where('status', 'rejected')->sum('count'),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'analytics' => [
                'total_tasks' => $tasks->count(),
                'active_tasks' => $tasks->where('status', 'active')->count(),
                'total_submissions' => $totalSubmissions,
                'approved_submissions' => $approvedSubmissions,
                'conversion_rate' => $conversionRate,
                'total_points_spent' => $totalPointsSpent,
                'top_tasks' => $topTasks,
                'daily_data' => $dailyData,
            ],
        ]);
    }
}
