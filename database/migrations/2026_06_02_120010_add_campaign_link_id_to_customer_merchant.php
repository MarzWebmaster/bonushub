<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_merchant', function (Blueprint $table) {
            $table->foreignId('campaign_link_id')->nullable()->after('tied_at')
                  ->constrained('campaign_links')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_merchant', function (Blueprint $table) {
            $table->dropForeign(['campaign_link_id']);
            $table->dropColumn('campaign_link_id');
        });
    }
};
