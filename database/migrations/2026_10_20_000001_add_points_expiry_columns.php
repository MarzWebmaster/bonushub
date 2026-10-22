<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Points expiry (PRD v1.2 4.2.2): per-merchant override of the expiry period + "already warned" marker. Additive. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_rates', function (Blueprint $t) {
            if (!Schema::hasColumn('loyalty_rates', 'points_expiry_months')) {
                $t->unsignedSmallInteger('points_expiry_months')->nullable();   // NULL = platform default, 0 = never expire
            }
        });
        Schema::table('customer_merchant', function (Blueprint $t) {
            if (!Schema::hasColumn('customer_merchant', 'expiry_warned_at')) {
                $t->timestamp('expiry_warned_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_merchant', function (Blueprint $t) {
            if (Schema::hasColumn('customer_merchant', 'expiry_warned_at')) {
                $t->dropColumn('expiry_warned_at');
            }
        });
        Schema::table('loyalty_rates', function (Blueprint $t) {
            if (Schema::hasColumn('loyalty_rates', 'points_expiry_months')) {
                $t->dropColumn('points_expiry_months');
            }
        });
    }
};
