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
        Schema::create('customer_merchant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->decimal('points', 15, 2)->default(0);
            $table->string('tier_per_merchant')->default('regular');
            $table->timestamp('tied_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'merchant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_merchant');
    }
};
