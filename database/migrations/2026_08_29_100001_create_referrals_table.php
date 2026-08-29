<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code', 32)->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->string('source')->nullable()->comment('e.g. facebook, instagram, whatsapp');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->integer('total_clicks')->default(0);
            $table->integer('total_signups')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->integer('points_earned')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
            $table->index('customer_id');
        });

        // Track individual referral clicks for analytics
        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamps();

            $table->index('referral_id');
        });

        // Add referred_by to customers table for tracking
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('referred_by')->nullable()->after('password')->constrained('customers')->nullOnDelete();
            $table->foreignId('referral_id')->nullable()->after('referred_by')->constrained('referrals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropForeign(['referral_id']);
            $table->dropColumn(['referred_by', 'referral_id']);
        });
        Schema::dropIfExists('referral_clicks');
        Schema::dropIfExists('referrals');
    }
};
