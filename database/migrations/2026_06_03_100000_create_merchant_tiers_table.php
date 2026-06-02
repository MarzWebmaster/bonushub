<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('merchants')->onDelete('cascade');
            $table->string('tier_name'); // Basic, Silver, Gold, Platinum
            $table->integer('min_points')->default(0);
            $table->timestamps();

            $table->unique(['merchant_id', 'tier_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_tiers');
    }
};
