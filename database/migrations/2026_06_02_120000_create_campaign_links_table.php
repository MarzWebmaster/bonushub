<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();
            $table->string('name');               // e.g. "IG Promo June"
            $table->string('slug')->unique();      // e.g. "kopia-ig"
            $table->string('medium')->nullable();  // instagram, facebook, whatsapp, flyer, etc
            $table->unsignedInteger('visits')->default(0);
            $table->unsignedInteger('registrations')->default(0);
            $table->string('status')->default('active'); // active / inactive / expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_links');
    }
};
