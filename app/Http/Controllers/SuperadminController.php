<?php

namespace App\Http\Controllers;

use App\Http\Requests\Superadmin\MerchantStoreRequest;
use App\Http\Requests\Superadmin\MerchantUpdateRequest;
use App\Http\Requests\Superadmin\PackageStoreRequest;
use App\Http\Requests\Superadmin\PackageUpdateRequest;
use App\Models\Merchant;
use App\Models\Package;
use App\Models\User;
use App\Models\PointsTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SuperadminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    // ========================
    // BLADE VIEWS
    // ========================

    public function dashboard(): View
    {
        return view('superadmin.dashboard', ['d' => $this->platformOverview()]);
    }

    /** Everything the superadmin dashboard shows, computed server-side (no loading placeholders). */
    private function platformOverview(): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $byStatus = Merchant::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $totalMerchants = (int) $byStatus->sum();

        // plans with their merchants (merchants without a package follow Free)
        $packages = Package::withCount('merchants')->orderBy('price')->get();
        $noPackage = Merchant::whereNull('package_id')->count();
        $maxPrice = (float) $packages->max('price');
        $plans = [];
        $mrr = 0.0;
        foreach ($packages as $p) {
            $count = (int) $p->merchants_count;
            if ((float) $p->price == 0.0 && $p === $packages->first()) {
                $count += $noPackage;            // Free carries the package-less merchants
            }
            $mrr += (float) $p->price * (int) $p->merchants_count;
            $plans[] = [
                'id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'merchants' => $count,
                'share' => $totalMerchants > 0 ? (int) round($count / $totalMerchants * 100) : 0,
                'tier' => (float) $p->price == 0.0 ? 'free' : (((float) $p->price === $maxPrice && $packages->count() > 1) ? 'top' : ''),
            ];
        }

        // points issued per day, last 14 days
        $from = $now->copy()->subDays(13)->startOfDay();
        $daily = PointsTransaction::where('type', 'earn')->where('status', 'approved')->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) d, SUM(points) s')->groupBy('d')->pluck('s', 'd');
        $series = [];
        for ($i = 0; $i < 14; $i++) {
            $day = $from->copy()->addDays($i);
            $series[] = ['label' => $day->format('d M'), 'day' => $day->format('j'), 'value' => (int) round((float) ($daily[$day->toDateString()] ?? 0))];
        }
        $peak = max(1, max(array_column($series, 'value')));
        foreach ($series as &$s) {
            $s['pct'] = (int) round($s['value'] / $peak * 100);
        }
        unset($s);
        $earn = fn ($days) => (int) round((float) PointsTransaction::where('type', 'earn')->where('status', 'approved')->where('created_at', '>=', $now->copy()->subDays($days))->sum('points'));

        $paidPackage = fn ($q) => $q->where('price', '>', 0);
        $thirty = $now->copy()->subDays(30);

        return [
            'generated_at' => $now->format('H:i'),
            'attention' => [
                'pending' => (int) ($byStatus['pending'] ?? 0),
                'expiring' => Merchant::whereHas('package', $paidPackage)->whereNotNull('subscription_expiry')->whereBetween('subscription_expiry', [$now, $now->copy()->addDays(7)])->count(),
                'lapsed' => Merchant::whereHas('package', $paidPackage)->whereNotNull('subscription_expiry')->where('subscription_expiry', '<', $now->copy()->subDays(7))->count(),
            ],
            'merchants' => [
                'total' => $totalMerchants, 'active' => (int) ($byStatus['active'] ?? 0), 'pending' => (int) ($byStatus['pending'] ?? 0),
                'rejected' => (int) ($byStatus['rejected'] ?? 0), 'new_month' => Merchant::where('created_at', '>=', $monthStart)->count(),
            ],
            'customers' => [
                'total' => DB::table('customers')->count(),
                'with_merchant' => DB::table('customer_merchant')->distinct()->count('customer_id'),
                'new_month' => DB::table('customers')->where('created_at', '>=', $monthStart)->count(),
            ],
            'staff' => User::role('staff')->count(),
            'mrr' => $mrr,
            'plans' => $plans,
            'no_package' => $noPackage,
            'chart' => $series,
            'points_14' => $earn(14),
            'points_30' => $earn(30),
            'usage' => [
                'redemptions' => DB::table('redemptions')->where('created_at', '>=', $thirty)->count(),
                'referrals' => DB::table('referral_signups')->where('status', 'valid')->where('validated_at', '>=', $thirty)->count(),
                'new_customers' => DB::table('customers')->where('created_at', '>=', $thirty)->count(),
                'giveaways' => DB::table('giveaway_campaigns')->where('status', 'active')->count(),
                'tasks' => DB::table('viral_tasks')->where('status', 'active')->count(),
                'campaign_clicks' => (int) DB::table('campaign_links')->sum('visits'),
            ],
            'latest' => Merchant::with('package:id,name')->orderByDesc('created_at')->orderByDesc('id')->limit(5)->get()->map(fn ($m) => [
                'id' => $m->id, 'name' => (string) $m->company_name, 'status' => (string) $m->status,
                'package' => $m->package?->name, 'joined' => $m->created_at?->format('d M Y') ?? '-',
            ])->all(),
            'system' => [
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'waiting_jobs' => DB::table('jobs')->count(),
                'expiry' => (bool) config('points.expiry_enabled'),
                'mailer' => (string) config('mail.default'),
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
            ],
        ];
    }

    public function merchantsPage(): View
    {
        $merchants = Merchant::withCount(['customers', 'pointsTransactions', 'users'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);
        $packages = Package::orderBy('price')->get();
        return view('superadmin.merchants', compact('merchants', 'packages'));
    }

    public function packagesPage(): View
    {
        $packages = Package::withCount('merchants')->orderBy('price')->paginate(12);
        $maxPrice = (float) Package::max('price');
        // the plan most merchants are on (only meaningful when somebody is on a plan)
        $topId = Package::withCount('merchants')->orderByDesc('merchants_count')->orderBy('price')->first();
        $topId = $topId && $topId->merchants_count > 0 ? $topId->id : null;
        $summary = [
            'plans'     => Package::count(),
            'merchants' => \App\Models\Merchant::whereNotNull('package_id')->count(),
            'paid'      => \App\Models\Merchant::whereHas('package', fn ($q) => $q->where('price', '>', 0))->count(),
        ];

        return view('superadmin.packages', compact('packages', 'maxPrice', 'topId', 'summary'));
    }

    // ========================
    // JSON API ENDPOINTS
    // ========================

    public function dashboardStats(): JsonResponse
    {
        $stats = [
            'total_merchants' => Merchant::count(),
            'active_merchants' => Merchant::where('status', 'active')->count(),
            'total_customers' => User::role('customer')->count(),
            'total_staff' => User::role('staff')->count(),
            'pending_approvals' => PointsTransaction::where('status', 'pending_approval')->count(),
            'total_packages' => Package::count(),
            'generated_at' => now()->toDateTimeString(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function merchants(): JsonResponse
    {
        $merchants = Merchant::withCount(['customers', 'pointsTransactions', 'users'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json(['success' => true, 'merchants' => $merchants]);
    }

    public function showMerchant(int $id): JsonResponse
    {
        $merchant = Merchant::with([
            'users:id,name,email',
            'loyaltyRate',
        ])->withCount(['customers', 'pointsTransactions', 'rewards'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'merchant' => $merchant]);
    }

    public function storeMerchant(MerchantStoreRequest $request): JsonResponse
    {
        try {
            $merchant = DB::transaction(function () use ($request) {
                $merchant = Merchant::create([
                    'company_name' => $request->merchant_name,
                    'owner_name'   => $request->name,
                    'phone'        => $request->phone,
                    'address'      => $request->address,
                    'status'       => $request->boolean('is_active', true) ? Merchant::STATUS_ACTIVE : 'inactive',
                    'package_id'   => $request->package_id,
                    'verified_at'  => now(),
                    'verified_by'  => auth()->id(),
                ]);

                $admin = User::create([
                    'name'        => $request->name,
                    'email'       => $request->email,
                    'password'    => $request->password,      // hashed by the model cast
                    'phone'       => $request->phone,
                    'merchant_id' => $merchant->id,
                    'status'      => 'active',
                    'role'        => 'merchant',
                ]);
                $admin->assignRole('merchant');

                return $merchant;
            });

            return response()->json([
                'success' => true,
                'message' => 'Merchant created successfully.',
                'merchant' => $merchant,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Superadmin create merchant failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create merchant. Please check the details and try again.',
            ], 500);
        }
    }

    public function updateMerchant(MerchantUpdateRequest $request, int $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        $data = $request->validated();

        DB::transaction(function () use ($merchant, $data) {
            $m = [];
            if (!empty($data['merchant_name'])) {
                $m['company_name'] = $data['merchant_name'];
            }
            foreach (['phone', 'address', 'package_id'] as $k) {
                if (array_key_exists($k, $data)) {
                    $m[$k] = $data[$k];
                }
            }
            if (array_key_exists('is_active', $data) && $data['is_active'] !== null) {
                $m['status'] = $data['is_active'] ? Merchant::STATUS_ACTIVE : 'inactive';
            }

            // the owner account (first merchant user): name / e-mail / password
            $owner = User::where('merchant_id', $merchant->id)->role('merchant')->orderBy('id')->first();
            if ($owner) {
                $u = [];
                foreach (['name', 'email'] as $k) {
                    if (!empty($data[$k])) {
                        $u[$k] = $data[$k];
                    }
                }
                if (!empty($data['password'])) {
                    $u['password'] = $data['password'];       // hashed by the model cast
                }
                if (!empty($data['name'])) {
                    $m['owner_name'] = $data['name'];
                }
                if ($u) {
                    $owner->update($u);
                }
            }

            if ($m) {
                $merchant->update($m);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Merchant updated successfully.',
            'merchant' => $merchant->fresh(),
        ]);
    }

    public function toggleMerchantStatus(int $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->update(['status' => $merchant->status === 'active' ? 'inactive' : 'active']);
        $status = $merchant->status === 'active' ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Merchant {$status} successfully.",
            'status' => $merchant->fresh()->status,
        ]);
    }

    public function destroyMerchant(int $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);

        // never hard-delete a merchant that has customers or a points ledger (cascades would destroy balances/history)
        $hasData = DB::table('customer_merchant')->where('merchant_id', $id)->exists()
            || DB::table('points_transactions')->where('merchant_id', $id)->exists();
        if ($hasData) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant ini mempunyai pelanggan/transaksi. Nyahaktifkan (deactivate) sebaliknya.',
            ], 409);
        }

        $merchantName = $merchant->company_name;
        // its logins must not outlive it (users.merchant_id is set to NULL when the merchant row goes)
        User::where('merchant_id', $id)->update(['status' => 'inactive']);
        $merchant->delete();

        return response()->json([
            'success' => true,
            'message' => "Merchant '{$merchantName}' deleted successfully.",
        ]);
    }

    public function packages(): JsonResponse
    {
        $packages = Package::orderBy('price')->paginate(5);
        return response()->json(['success' => true, 'packages' => $packages]);
    }

    public function showPackage(int $id): JsonResponse
    {
        $package = Package::findOrFail($id);
        return response()->json(['success' => true, 'package' => $package]);
    }

    public function storePackage(PackageStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $row = [
            'name'           => $data['name'],
            'price'          => $data['price'],
            // -1 = unlimited. Defaults follow the Free plan when the form does not send a value.
            'branch_limit'   => $data['branch_limit'] ?? 1,
            'staff_limit'    => $data['staff_limit'] ?? 2,
            'giveaway_limit' => $data['giveaway_limit'] ?? 0,
            'task_limit'     => $data['task_limit'] ?? 0,
        ];
        if (isset($data['features']) && is_array($data['features'])) {
            $row['features'] = json_encode($data['features']);
        }
        $package = Package::create($row);

        return response()->json([
            'success' => true,
            'message' => 'Package created successfully.',
            'package' => $package,
        ], 201);
    }

    public function updatePackage(PackageUpdateRequest $request, int $id): JsonResponse
    {
        $package = Package::findOrFail($id);
        $data = $request->validated();
        $row = [];
        foreach (['name', 'price', 'branch_limit', 'staff_limit', 'giveaway_limit', 'task_limit'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $row[$k] = $data[$k];
            }
        }
        if (isset($data['features']) && is_array($data['features'])) {
            $row['features'] = json_encode($data['features']);
        }
        $package->update($row);

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully.',
            'package' => $package->fresh(),
        ]);
    }

    public function destroyPackage(int $id): JsonResponse
    {
        $package = Package::findOrFail($id);
        $inUse = $package->merchants()->count();
        if ($inUse > 0) {
            return response()->json([
                'success' => false,
                'message' => "{$inUse} merchant(s) are on this package. Move them to another package first.",
            ], 422);
        }
        $packageName = $package->name;
        $package->delete();

        return response()->json([
            'success' => true,
            'message' => "Package '{$packageName}' deleted successfully.",
        ]);
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $query = \App\Models\ActivityLog::with('user:id,name');

        if ($request->has('user_id'))
            $query->where('user_id', $request->user_id);
        if ($request->has('action'))
            $query->where('action', $request->action);
        if ($request->has('date_from'))
            $query->where('created_at', '>=', $request->date_from);
        if ($request->has('date_to'))
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json(['success' => true, 'logs' => $logs]);
    }

    public function auditLogsPage(): View
    {
        $logs = \App\Models\ActivityLog::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        return view('superadmin.audit', compact('logs'));
    }

    public function globalLeaderboard(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);
        $page = max((int) $request->query('page', 1), 1);

        $query = DB::table('customer_merchant')
            ->join('customers', 'customer_merchant.customer_id', '=', 'customers.id')
            ->leftJoin('merchants', 'customer_merchant.merchant_id', '=', 'merchants.id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.tier_global',
                DB::raw('SUM(customer_merchant.points) as total_points'),
                DB::raw('COUNT(DISTINCT customer_merchant.merchant_id) as merchant_count'),
                DB::raw("GROUP_CONCAT(DISTINCT merchants.company_name ORDER BY merchants.company_name SEPARATOR ', ') as merchant_names")
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.tier_global')
            ->orderBy('total_points', 'desc');

        $total = DB::table('customer_merchant')
            ->join('customers', 'customer_merchant.customer_id', '=', 'customers.id')
            ->select('customers.id')
            ->groupBy('customers.id')
            ->get()->count();

        $leaderboard = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($entry, $index) use ($page, $perPage) {
                $entry->rank = (($page - 1) * $perPage) + $index + 1;
                return $entry;
            });

        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    public function leaderboardPage(): View
    {
        return view('superadmin.leaderboard');
    }

    public function showMerchantPage(int $id): View
    {
        $merchant = Merchant::with(['users:id,name,email', 'branches', 'loyaltyRate', 'package'])
            ->withCount(['customers', 'pointsTransactions', 'rewards'])
            ->findOrFail($id);

        $recentTransactions = \App\Models\PointsTransaction::where('merchant_id', $id)
            ->latest()
            ->take(10)
            ->get();

        $rewards = \App\Models\MerchantReward::where('merchant_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $packages = \App\Models\Package::orderBy('price')->get();

        return view('superadmin.merchant-detail', compact('merchant', 'recentTransactions', 'rewards', 'packages'));
    }

    // ========================
    // MERCHANT APPROVAL
    // ========================

    /**
     * Stream a merchant's verification document (IC front/back, SSM) to a superadmin.
     * Documents live on the private disk, so this is the only way to view them.
     */
    public function merchantDocument(int $id, string $type)
    {
        $column = ['ic' => 'ic_image', 'ic_back' => 'ic_image_back', 'ssm' => 'ssm_image'][$type] ?? null;
        abort_if($column === null, 404);

        $path = Merchant::findOrFail($id)->{$column};
        abort_unless($path, 404);

        // Paths are generated by Laravel's store(), but never trust a DB value blindly (no traversal).
        abort_unless(preg_match('#^verification/(ic|ssm)/[A-Za-z0-9._-]+$#', $path) === 1, 404);

        // 'local' = private (current). 'public' = legacy location for documents uploaded
        // before this change, kept as a fallback until they have been moved.
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Log::info('Verification document viewed', [
                    'merchant_id' => $id,
                    'type'        => $type,
                    'by_user_id'  => auth()->id(),
                ]);

                return Storage::disk($disk)->response($path, null, [
                    'Cache-Control'          => 'private, no-store',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        abort(404);
    }

    public function pendingMerchantsPage(): View
    {
        $merchants = Merchant::pending()
            ->withCount(['users'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('superadmin.merchants-pending', compact('merchants'));
    }

    public function pendingMerchants(): JsonResponse
    {
        $merchants = Merchant::pending()
            ->withCount(['users'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['success' => true, 'merchants' => $merchants]);
    }

    public function approveMerchant(int $id): JsonResponse
    {
        try {
            $merchant = Merchant::findOrFail($id);

            $merchant->update([
                'status'      => Merchant::STATUS_ACTIVE,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'rejection_reason' => null,
            ]);

            // ── SEND NOTIFICATION TO MERCHANT ──
            // Via BonusNotifier (toast + email)
            $merchantUser = $merchant->users()->first();
            if ($merchantUser) {
                // Email notification
                try {
                    Mail::raw("Tahniah, {$merchantUser->name}!\n\n"
                        . "Akaun merchant anda untuk **{$merchant->company_name}** telah diluluskan oleh admin.\n\n"
                        . "Anda kini boleh log masuk dan menggunakan semua ciri BonusHub.\n\n"
                        . "👉 " . route('login') . "\n\n"
                        . "Terima kasih,\n"
                        . "Pasukan BonusHub",
                        function ($message) use ($merchantUser, $merchant) {
                            $message->to($merchantUser->email)
                                ->subject("✅ Akaun Merchant Diluluskan — {$merchant->company_name}")
                                ->from('noreply@bonushub.my', 'BonusHub');
                        }
                    );
                } catch (\Exception $e) {
                    Log::warning("Failed to send approval email to {$merchantUser->email}: " . $e->getMessage());
                }

                // BonusNotifier (in-app notification)
                if (class_exists(\App\Models\NotificationSetting::class) && \App\Models\NotificationSetting::count() === 0) {
                    \App\Models\NotificationSetting::seedDefaults();
                }
                if (class_exists(\App\Services\BonusNotifier::class)) {
                    \App\Services\BonusNotifier::toMerchant($merchant, 'merchant_approved', [
                        'title'   => '✅ Akaun Merchant Diluluskan!',
                        'message' => "Tahniah! Akaun merchant anda untuk **{$merchant->company_name}** telah diluluskan.\n\n"
                            . "Anda kini boleh menggunakan semua ciri BonusHub.",
                        'type'    => 'success',
                        'data'    => [
                            'merchant_id'  => $merchant->id,
                            'company_name' => $merchant->company_name,
                            'redirect_url' => route('merchant.dashboard'),
                        ],
                    ]);
                }
            }

            Log::info("Merchant approved: {$merchant->company_name} by admin #" . auth()->id(), [
                'merchant_id' => $merchant->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Merchant '{$merchant->company_name}' telah diluluskan. Notifikasi dihantar kepada merchant.",
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to approve merchant #{$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal meluluskan merchant: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rejectMerchant(int $id): JsonResponse
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $reason = request()->input('reason', '');

            $merchant->update([
                'status'           => Merchant::STATUS_REJECTED,
                'rejection_reason' => $reason ?: 'Ditolak oleh admin',
                'verified_at'      => null,
                'verified_by'      => null,
            ]);

            // ── SEND NOTIFICATION TO MERCHANT ──
            $merchantUser = $merchant->users()->first();
            if ($merchantUser) {
                // Email notification
                try {
                    Mail::raw("Hai {$merchantUser->name},\n\n"
                        . "Maaf, akaun merchant anda untuk **{$merchant->company_name}** telah ditolak.\n\n"
                        . "Sebab: " . ($reason ?: 'Tiada sebab diberi') . "\n\n"
                        . "Sila muat naik semula dokumen yang betul:\n"
                        . "👉 " . route('merchant.verification') . "\n\n"
                        . "Terima kasih,\n"
                        . "Pasukan BonusHub",
                        function ($message) use ($merchantUser, $merchant) {
                            $message->to($merchantUser->email)
                                ->subject("❌ Akaun Merchant Ditolak — {$merchant->company_name}")
                                ->from('noreply@bonushub.my', 'BonusHub');
                        }
                    );
                } catch (\Exception $e) {
                    Log::warning("Failed to send rejection email to {$merchantUser->email}: " . $e->getMessage());
                }

                // BonusNotifier (in-app notification)
                if (class_exists(\App\Services\BonusNotifier::class)) {
                    \App\Services\BonusNotifier::toMerchant($merchant, 'merchant_rejected', [
                        'title'   => '❌ Akaun Merchant Ditolak',
                        'message' => "Maaf, akaun merchant anda untuk **{$merchant->company_name}** telah ditolak.\n\n"
                            . "Sebab: " . ($reason ?: 'Tiada sebab diberi') . "\n\n"
                            . "Sila muat naik semula dokumen yang betul.",
                        'type'    => 'error',
                        'data'    => [
                            'merchant_id'  => $merchant->id,
                            'company_name' => $merchant->company_name,
                            'redirect_url' => route('merchant.verification'),
                        ],
                    ]);
                }
            }

            Log::info("Merchant rejected: {$merchant->company_name} by admin #" . auth()->id(), [
                'merchant_id' => $merchant->id,
                'reason'      => $reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Merchant '{$merchant->company_name}' telah ditolak. Notifikasi dihantar kepada merchant.",
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to reject merchant #{$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak merchant: ' . $e->getMessage(),
            ], 500);
        }
    }
}
