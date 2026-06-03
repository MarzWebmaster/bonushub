<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['registration_bonus', 'multiplier', 'fixed_bonus']);
            $table->decimal('value', 10, 2); // 100 points, 2.0 multiplier
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
            $table->index(['merchant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
