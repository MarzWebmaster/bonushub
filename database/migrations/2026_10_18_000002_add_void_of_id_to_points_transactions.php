<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A void is a reversing 'void' row that points at the transaction it cancels. The UNIQUE index makes it
 * impossible to void the same transaction twice, even under concurrent requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('points_transactions', 'void_of_id')) {
            Schema::table('points_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('void_of_id')->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('points_transactions', 'void_of_id')) {
            Schema::table('points_transactions', function (Blueprint $table) {
                $table->dropUnique(['void_of_id']);
                $table->dropColumn('void_of_id');
            });
        }
    }
};
