<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_merchant', function (Blueprint $table) {
            $table->index(['merchant_id', 'points'], 'cm_merchant_points_idx');
            $table->index(['merchant_id', 'tier_per_merchant'], 'cm_merchant_tier_idx');
        });
    }

    public function down(): void
    {
        Schema::table('customer_merchant', function (Blueprint $table) {
            $table->dropIndex('cm_merchant_points_idx');
            $table->dropIndex('cm_merchant_tier_idx');
        });
    }
};
