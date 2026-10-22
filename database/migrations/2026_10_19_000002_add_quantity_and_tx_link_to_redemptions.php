<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A redemption now remembers how many units it took and which ledger row paid for it, so that cancelling or voiding it
 * can put the stock back and the two refund paths (merchant "cancel" and staff "void") cannot both refund the same points.
 * Additive; existing rows default to quantity 1 and no link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redemptions', function (Blueprint $t) {
            if (!Schema::hasColumn('redemptions', 'quantity')) {
                $t->unsignedInteger('quantity')->default(1);
            }
            if (!Schema::hasColumn('redemptions', 'points_transaction_id')) {
                $t->unsignedBigInteger('points_transaction_id')->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $t) {
            if (Schema::hasColumn('redemptions', 'points_transaction_id')) {
                $t->dropUnique(['points_transaction_id']);
                $t->dropColumn('points_transaction_id');
            }
            if (Schema::hasColumn('redemptions', 'quantity')) {
                $t->dropColumn('quantity');
            }
        });
    }
};
