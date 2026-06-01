<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\MerchantAdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SuperadminController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('superadmin')) return redirect()->route('superadmin.dashboard');
    if ($user->hasRole('merchant')) return redirect()->route('merchant.dashboard');
    if ($user->hasRole('staff')) return redirect()->route('staff.dashboard');
    if ($user->hasRole('customer')) return redirect()->route('customer.dashboard');
    return redirect()->route('home');
})->name('dashboard')->middleware('auth');

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Existing management routes
Route::get('/manage/shop', [ShopController::class, 'index'])->name('manage.shop');
Route::get('/manage/shop/package', [PackageController::class, 'index'])->name('manage.shop.package');
Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
Route::middleware('auth')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::post('/settings/password', [SettingsController::class, 'password'])->name('settings.password');
});

// ========================
// Staff Routes
// ========================
Route::prefix('staff')->name('staff.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::post('/customer-lookup', [StaffController::class, 'customerLookup'])->name('customer.lookup');
    Route::post('/add-points', [StaffController::class, 'addPoints'])->name('add.points');
    Route::post('/redeem', [StaffController::class, 'redeemPoints'])->name('redeem');
    Route::post('/void', [StaffController::class, 'voidTransaction'])->name('void');
});

// ========================
// Merchant Admin Routes
// ========================
Route::prefix('merchant')->name('merchant.')->middleware(['auth'])->group(function () {
    // Pages (HTML views)
    Route::get('/dashboard', [MerchantAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/points/pending', [MerchantAdminController::class, 'pendingApprovalsPage'])->name('points.pending');
    Route::post('/points/approve/{id}', [MerchantAdminController::class, 'approvePoints'])->name('points.approve');
    Route::post('/points/reject/{id}', [MerchantAdminController::class, 'rejectPoints'])->name('points.reject');

    // Reward products (HTML page)
    Route::get('/rewards', [MerchantAdminController::class, 'rewardProductsPage'])->name('rewards.index');
    Route::post('/rewards', [MerchantAdminController::class, 'storeRewardProduct'])->name('rewards.store');
    Route::delete('/rewards/{id}', [MerchantAdminController::class, 'destroyRewardProduct'])->name('rewards.destroy');

    // Customers (HTML page)
    Route::get('/customers', [MerchantAdminController::class, 'customerListPage'])->name('customers');

    // Leaderboard (HTML page)
    Route::get('/leaderboard', [MerchantAdminController::class, 'leaderboardPage'])->name('leaderboard');

    // Reports (HTML page)
    Route::get('/reports/liability', [MerchantAdminController::class, 'liabilityReportPage'])->name('reports.liability');

    // Loyalty rate settings (HTML page)
    Route::get('/loyalty-rates', [MerchantAdminController::class, 'loyaltyRatesPage'])->name('loyalty.rates');
    Route::post('/loyalty-rates', [MerchantAdminController::class, 'updateLoyaltyRates'])->name('loyalty.rates.update');

    // JSON API endpoints (for AJAX)
    Route::get('/api/dashboard', [MerchantAdminController::class, 'dashboardStats'])->name('api.dashboard');
    Route::get('/api/points/pending', [MerchantAdminController::class, 'pendingApprovals'])->name('api.points.pending');
    Route::get('/api/rewards', [MerchantAdminController::class, 'rewardProducts'])->name('api.rewards');
    Route::put('/api/rewards/{id}', [MerchantAdminController::class, 'updateRewardProduct'])->name('api.rewards.update');
    Route::get('/api/customers', [MerchantAdminController::class, 'customerListByTier'])->name('api.customers');
    Route::get('/api/leaderboard', [MerchantAdminController::class, 'leaderboard'])->name('api.leaderboard');
    Route::get('/api/reports/liability', [MerchantAdminController::class, 'liabilityReport'])->name('api.reports.liability');
    Route::get('/api/loyalty-rates', [MerchantAdminController::class, 'getLoyaltyRates'])->name('api.loyalty.rates');
});

// ========================
// Customer Routes
// ========================
Route::prefix('customer')->name('customer.')->middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
    Route::post('/profile', [CustomerController::class, 'updateProfile'])->name('profile.update');

    // Points
    Route::get('/points', [CustomerController::class, 'pointsBalance'])->name('points');
    Route::get('/points/history', [CustomerController::class, 'pointsHistory'])->name('points.history');

    // Rewards
    Route::get('/rewards', [CustomerController::class, 'availableRewards'])->name('rewards');
    Route::post('/redeem', [CustomerController::class, 'redeemReward'])->name('redeem');

    // Dashboard
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');

    // Leaderboard
    Route::get('/leaderboard', [CustomerController::class, 'leaderboardPage'])->name('leaderboard');

    // JSON API endpoints
    Route::get('/api/points', [CustomerController::class, 'pointsBalance'])->name('api.points');
    Route::get('/api/rewards', [CustomerController::class, 'availableRewards'])->name('api.rewards');
    Route::get('/api/leaderboard', [CustomerController::class, 'leaderboard'])->name('api.leaderboard');
    Route::get('/api/profile', [CustomerController::class, 'profileApi'])->name('api.profile');
});

// ========================
// Superadmin Routes
// ========================
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth'])->group(function () {
    // Pages (HTML views)
    Route::get('/dashboard', [SuperadminController::class, 'dashboard'])->name('dashboard');
    Route::get('/merchants', [SuperadminController::class, 'merchantsPage'])->name('merchants');
    Route::get("/merchants/{id}", [SuperadminController::class, "showMerchantPage"])->name("merchants.show");
    Route::get('/packages', [SuperadminController::class, 'packagesPage'])->name('packages');
    Route::get('/audit', [SuperadminController::class, 'auditLogsPage'])->name('audit');
    Route::get('/leaderboard', [SuperadminController::class, 'leaderboardPage'])->name('leaderboard');

    // JSON API endpoints (for AJAX)
    Route::get('/api/stats', [SuperadminController::class, 'dashboardStats'])->name('dashboard.stats');

    // Merchants CRUD (API)
    Route::get('/api/merchants', [SuperadminController::class, 'merchants'])->name('api.merchants');
    Route::get('/api/merchants/{id}', [SuperadminController::class, 'showMerchant'])->name('api.merchants.show');
    Route::post('/api/merchants', [SuperadminController::class, 'storeMerchant'])->name('api.merchants.store');
    Route::put('/api/merchants/{id}', [SuperadminController::class, 'updateMerchant'])->name('api.merchants.update');
    Route::post('/api/merchants/{id}/toggle', [SuperadminController::class, 'toggleMerchantStatus'])->name('api.merchants.toggle');
    Route::delete('/api/merchants/{id}', [SuperadminController::class, 'destroyMerchant'])->name('api.merchants.destroy');

    // Packages CRUD (API)
    Route::get('/api/packages', [SuperadminController::class, 'packages'])->name('api.packages');
    Route::get('/api/packages/{id}', [SuperadminController::class, 'showPackage'])->name('api.packages.show');
    Route::post('/api/packages', [SuperadminController::class, 'storePackage'])->name('api.packages.store');
    Route::put('/api/packages/{id}', [SuperadminController::class, 'updatePackage'])->name('api.packages.update');
    Route::delete('/api/packages/{id}', [SuperadminController::class, 'destroyPackage'])->name('api.packages.destroy');

    // Audit & Leaderboard (API)
    Route::get('/api/audit-logs', [SuperadminController::class, 'auditLogs'])->name('api.audit');
    Route::get('/api/leaderboard', [SuperadminController::class, 'globalLeaderboard'])->name('api.leaderboard');
});
