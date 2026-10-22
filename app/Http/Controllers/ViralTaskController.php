<?php

namespace App\Http\Controllers;

use App\Models\ViralTask;
use App\Models\TaskSubmission;
use App\Models\Customer;
use App\Models\PointsTransaction;
use App\Traits\SendsEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ViralTaskController extends Controller
{
    use SendsEmails;
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
            ->with(['customer', 'duplicateOf.customer'])
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

        if ($blocked = $this->taskLimitReached($this->getMerchantId())) {
            return response()->json(['success' => false, 'message' => $blocked], 422);
        }

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

    /** null when another ACTIVE task may be added, else the message to show (package task_limit: -1 unlimited, 0 none). */
    private function taskLimitReached(int $merchantId): ?string
    {
        $merchant = \App\Models\Merchant::find($merchantId);
        $limit = $merchant ? $merchant->limit('task_limit') : -1;
        if ($limit < 0) {
            return null;
        }
        if ($limit === 0) {
            return 'Pakej anda tidak termasuk tugasan viral. Naik taraf pakej untuk menggunakannya.';
        }
        if (ViralTask::forMerchant($merchantId)->where('status', 'active')->count() >= $limit) {
            return "Had tugasan aktif pakej anda ({$limit}) telah dicapai. Jeda/tamatkan tugasan lain atau naik taraf pakej.";
        }
        return null;
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

        // re-activating a paused/completed task counts against the package's active-task limit
        if (($validated['status'] ?? null) === 'active' && $task->status !== 'active') {
            if ($blocked = $this->taskLimitReached($merchantId)) {
                return response()->json(['success' => false, 'message' => $blocked], 422);
            }
        }

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

        $merchantId = $this->getMerchantId();
        $submission = TaskSubmission::whereHas('task', function ($q) use ($merchantId) {
            $q->where('merchant_id', $merchantId);
        })->findOrFail($submissionId);
        $approve = $validated['action'] === 'approve';

        try {
            $outcome = \Illuminate\Support\Facades\DB::transaction(function () use ($submission, $validated, $approve, $merchantId) {
                // Atomic claim: only ONE concurrent request can move pending -> reviewed, so a double click or two
                // reviewers can never credit the points twice. The credit below is in the same transaction, so a
                // submission can never end up "approved" without its points.
                $claimed = TaskSubmission::where('id', $submission->id)->where('status', 'pending')->update([
                    'status' => $approve ? 'approved' : 'rejected',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                    'review_notes' => $validated['review_notes'] ?? null,
                ]);
                if (!$claimed) {
                    return null;
                }

                if (!$approve) {
                    return ['task' => null];
                }

                $task = $submission->task;
                TaskSubmission::where('id', $submission->id)->update(['points_awarded' => $task->points_reward]);

                // Update task counters
                $task->increment('current_completions');
                $task->increment('total_points_spent', $task->points_reward);

                // Credit customer points (joins the merchant's program automatically if not yet a member)
                $customer = $submission->customer;
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
                        'tied_at' => now(),
                    ]);
                }

                PointsTransaction::create([
                    'customer_id' => $customer->id,
                    'merchant_id' => $merchantId,
                    'type' => 'earn',
                    'points' => $task->points_reward,
                    'status' => 'approved',
                    'notes' => "Viral task: {$task->title} (submission #{$submission->id})",
                    'staff_id' => Auth::id(),
                ]);

                return ['task' => $task, 'customer_id' => $customer->id];
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Task review failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memproses semakan. Sila cuba lagi.'], 500);
        }

        if ($outcome === null) {
            return response()->json(['success' => false, 'message' => 'Already reviewed.'], 422);
        }

        // e-mail only after the transaction has committed (never blocks or rolls back the credit)
        if ($approve && !empty($outcome['task'])) {
            $this->sendTaskApprovedEmail($outcome['customer_id'], $outcome['task']->title, $outcome['task']->points_reward, $merchantId);
        }

        return response()->json([
            'success' => true,
            'submission' => $submission->fresh(['customer']),
            'message' => $approve ? 'Approved + points credited!' : 'Submission rejected.',
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

        // Rate limit: max 5 submissions per customer per day
        $todaySubmissions = TaskSubmission::where('customer_id', $customer->id)
            ->whereDate('created_at', today())
            ->count();
        if ($todaySubmissions >= 5) {
            return response()->json(['success' => false, 'message' => 'Daily submission limit reached (5/day). Try again tomorrow!'], 429);
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

        // Duplicate-proof detection: SHA-256 of the image and of the normalised link, compared with this merchant's
        // pending/approved submissions (rejected ones do not count).
        //   same customer re-using a proof on another task of the merchant -> refused
        //   another customer sent the same proof                           -> accepted but flagged for the merchant
        $imageHash = $request->hasFile('screenshot') ? hash_file('sha256', $request->file('screenshot')->getRealPath()) : null;
        $urlHash = !empty($validated['proof_url']) ? hash('sha256', self::normaliseProofUrl($validated['proof_url'])) : null;
        $duplicate = null;
        if ($imageHash || $urlHash) {
            $duplicate = TaskSubmission::whereIn('task_id', ViralTask::where('merchant_id', $task->merchant_id)->pluck('id'))
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($w) use ($imageHash, $urlHash) {
                    if ($imageHash) { $w->orWhere('screenshot_hash', $imageHash); }
                    if ($urlHash) { $w->orWhere('proof_url_hash', $urlHash); }
                })
                ->orderBy('id')->first();
        }
        if ($duplicate && (int) $duplicate->customer_id === (int) $customer->id) {
            return response()->json(['success' => false, 'message' => 'Anda sudah menghantar bukti yang sama untuk tugasan lain. Hantar bukti baharu.'], 422);
        }

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
            'screenshot_hash' => $imageHash,
            'proof_url_hash' => $urlHash,
            'duplicate_of_id' => $duplicate?->id,
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

    /** Lower-case host, no fragment, no trailing slash, no utm_/fbclid/gclid tracking parameters, sorted query. */
    public static function normaliseProofUrl(string $url): string
    {
        $p = parse_url(trim($url));
        if (!$p || empty($p['host'])) {
            return strtolower(trim($url));
        }
        parse_str($p['query'] ?? '', $q);
        foreach (array_keys($q) as $k) {
            if (str_starts_with(strtolower((string) $k), 'utm_') || in_array(strtolower((string) $k), ['fbclid', 'gclid'], true)) {
                unset($q[$k]);
            }
        }
        ksort($q);

        return strtolower($p['scheme'] ?? 'https') . '://' . strtolower($p['host']) . (isset($p['port']) ? ':' . $p['port'] : '')
            . rtrim($p['path'] ?? '', '/') . ($q ? '?' . http_build_query($q) : '');
    }

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
