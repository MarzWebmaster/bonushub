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
        Schema::create('merchant_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('points_required', 15, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('stock_left')->default(0);
            $table->enum('claim_type', ['self_collect', 'delivery', 'download', 'access_code'])->default('self_collect');
            $table->enum('delivery_cost', ['merchant', 'customer', 'none'])->default('none');
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->string('download_url')->nullable();
            $table->string('access_code_prefix')->nullable();
            $table->string('status')->default('active');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_rewards');
    }
};
