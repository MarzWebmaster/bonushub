<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('email');
            $table->unsignedBigInteger('merchant_id')->nullable()->after('role');
            $table->unsignedBigInteger('branch_id')->nullable()->after('merchant_id');
            $table->string('phone', 20)->nullable()->after('branch_id');
            $table->string('status')->default('active')->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('profile_picture')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'merchant_id',
                'branch_id',
                'phone',
                'status',
                'last_login_at',
                'profile_picture',
            ]);
        });
    }
};
