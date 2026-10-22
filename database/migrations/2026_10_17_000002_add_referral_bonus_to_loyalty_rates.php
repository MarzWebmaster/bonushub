<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Merchant-set referral bonus (points). 0 = disabled, so no points are ever issued on a
 * merchant's behalf until the merchant opts in on the Loyalty Rates page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_rates', 'referral_bonus_referrer')) {
                $table->unsignedInteger('referral_bonus_referrer')->default(0);
            }
            if (!Schema::hasColumn('loyalty_rates', 'referral_bonus_referred')) {
                $table->unsignedInteger('referral_bonus_referred')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_rates', function (Blueprint $table) {
            foreach (['referral_bonus_referrer', 'referral_bonus_referred'] as $col) {
                if (Schema::hasColumn('loyalty_rates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
