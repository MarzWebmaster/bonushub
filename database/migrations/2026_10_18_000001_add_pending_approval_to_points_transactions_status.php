<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HOTFIX: the approval workflow (ScanController, MerchantAdminController::pendingApprovals/approve/reject,
 * dashboards) uses the status 'pending_approval', but points_transactions.status was
 * enum('pending','approved','rejected'), so inserting 'pending_approval' fails under MySQL strict mode
 * ("Data truncated for column 'status'"). Add the value; keep 'pending' so existing rows stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('points_transactions')) {
            return;
        }
        DB::statement("ALTER TABLE points_transactions MODIFY status ENUM('pending','pending_approval','approved','rejected') NOT NULL DEFAULT 'approved'");
    }

    public function down(): void
    {
        // rows still waiting for approval fall back to the legacy 'pending' value
        DB::table('points_transactions')->where('status', 'pending_approval')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE points_transactions MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved'");
    }
};
