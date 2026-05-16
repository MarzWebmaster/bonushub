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
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained('merchant_rewards')->cascadeOnDelete();
            $table->decimal('points_used', 15, 2)->default(0);
            $table->decimal('cash_topup', 15, 2)->default(0);
            $table->string('claim_method')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->string('claim_code')->nullable()->unique();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
