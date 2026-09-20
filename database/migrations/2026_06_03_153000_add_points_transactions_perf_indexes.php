<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points_transactions', function (Blueprint $table) {
            $table->index(['merchant_id', 'status'], 'pt_merchant_status_idx');
            $table->index(['merchant_id', 'type'], 'pt_merchant_type_idx');
            $table->index(['merchant_id', 'created_at'], 'pt_merchant_created_idx');
            $table->index(['merchant_id', 'customer_id', 'created_at'], 'pt_merchant_cust_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('points_transactions', function (Blueprint $table) {
            $table->dropIndex('pt_merchant_status_idx');
            $table->dropIndex('pt_merchant_type_idx');
            $table->dropIndex('pt_merchant_created_idx');
            $table->dropIndex('pt_merchant_cust_created_idx');
        });
    }
};
