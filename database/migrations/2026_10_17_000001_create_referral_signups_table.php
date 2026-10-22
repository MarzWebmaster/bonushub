<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD v1.2 section 5.3.6 / 5.4: attribute customer referral links (/ref/{code}) to registrations.
 * One row per referred customer; the daily `referrals:validate` job promotes pending rows to valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referral_signups')) {
            return;
        }

        Schema::create('referral_signups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referral_id')->index();
            $table->unsignedBigInteger('referred_customer_id')->unique();
            $table->enum('status', ['pending', 'valid', 'invalid'])->default('pending')->index();
            $table->string('invalid_reason')->nullable();
            // HMAC of the signup IP (never the raw IP) - used only to flag duplicate signups.
            $table->string('signup_ip_hash', 64)->nullable()->index();
            $table->json('flags')->nullable();
            $table->unsignedInteger('points_referrer')->default(0);
            $table->unsignedInteger('points_referred')->default(0);
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_signups');
    }
};
