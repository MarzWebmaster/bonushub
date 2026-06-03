<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('loyalty_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_rates', 'earn_rate')) {
                $table->decimal('earn_rate', 10, 2)->default(1.00)->after('rate_per_rm');
            }
            if (!Schema::hasColumn('loyalty_rates', 'redeem_rate')) {
                $table->decimal('redeem_rate', 10, 2)->default(1.00)->after('earn_rate');
            }
            if (!Schema::hasColumn('loyalty_rates', 'min_redeem')) {
                $table->integer('min_redeem')->nullable()->after('redeem_rate');
            }
            if (!Schema::hasColumn('loyalty_rates', 'max_redeem')) {
                $table->integer('max_redeem')->nullable()->after('min_redeem');
            }
        });
    }
    public function down(): void {
        Schema::table('loyalty_rates', function (Blueprint $table) {
            $table->dropColumn(['earn_rate', 'redeem_rate', 'min_redeem', 'max_redeem']);
        });
    }
};
