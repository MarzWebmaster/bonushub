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
        return view('superadmin.dashboard');
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
        $packages = Package::orderBy('price')->paginate(5);
        return view('superadmin.packages', compact('packages'));
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
            DB::beginTransaction();

            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'merchant_id' => 0,
            ]);
            $admin->assignRole('merchant');

            $package_id = $request->package_id;
            $package = Package::find($package_id);

            $merchant = Merchant::create([
                'name' => $request->merchant_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status ?? 'active',
                'package_id' => $package_id,
                'branch_limit' => $package->branch_limit ?? 1,
                'staff_limit' => $package->staff_limit ?? 2,
            ]);

            $admin->update(['merchant_id' => $merchant->id]);

            DB::table('merchant_user')->insert([
                'merchant_id' => $merchant->id,
                'user_id' => $admin->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merchant created successfully.',
                'merchant' => $merchant,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create merchant: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateMerchant(MerchantUpdateRequest $request, int $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        $data = $request->validated();
        // Map merchant_name to company_name (model field)
        if (isset($data["merchant_name"])) {
            $data["company_name"] = $data["merchant_name"];
            unset($data["merchant_name"]);
        }
        $merchant->update($data);

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
        $merchantName = $merchant->name;
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
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        $package = Package::create($data);

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
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        $package->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully.',
            'package' => $package->fresh(),
        ]);
    }

    public function destroyPackage(int $id): JsonResponse
    {
        $package = Package::findOrFail($id);
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
        $limit = min((int) $request->query('limit', 50), 200);

        $leaderboard = DB::table('customer_merchant')
            ->join('customers', 'customer_merchant.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.tier_global',
                DB::raw('SUM(customer_merchant.points) as total_points'),
                DB::raw('COUNT(DISTINCT customer_merchant.merchant_id) as merchant_count')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.tier_global')
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($entry, $index) {
                $entry->rank = $index + 1;
                return $entry;
            });

        return response()->json(['success' => true, 'leaderboard' => $leaderboard]);
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
