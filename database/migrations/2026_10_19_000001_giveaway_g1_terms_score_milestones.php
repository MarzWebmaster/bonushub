<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PRD v1.2 G1: giveaway = merit contest scored by valid referrals.
 *  - campaigns: T&C text, threshold score, milestone ladder, two more selection methods
 *  - entries: referral score + proof of T&C acceptance
 * Additive only (nothing dropped, 'random' stays in the enum but the controller rejects it, D1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giveaway_campaigns', function (Blueprint $t) {
            if (!Schema::hasColumn('giveaway_campaigns', 'terms')) {
                $t->text('terms')->nullable();
            }
            if (!Schema::hasColumn('giveaway_campaigns', 'threshold_score')) {
                $t->unsignedInteger('threshold_score')->nullable();
            }
            if (!Schema::hasColumn('giveaway_campaigns', 'milestones')) {
                $t->json('milestones')->nullable();
            }
        });

        Schema::table('giveaway_entries', function (Blueprint $t) {
            if (!Schema::hasColumn('giveaway_entries', 'score')) {
                $t->unsignedInteger('score')->default(0);
            }
            if (!Schema::hasColumn('giveaway_entries', 'terms_accepted_at')) {
                $t->timestamp('terms_accepted_at')->nullable();
            }
            if (!Schema::hasColumn('giveaway_entries', 'terms_hash')) {
                $t->char('terms_hash', 64)->nullable();
            }
        });

        DB::statement("ALTER TABLE giveaway_campaigns MODIFY selection_method ENUM('manual','random','top_referrers','threshold','milestone') NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        // The enum is left widened on purpose: narrowing it could fail on rows already using the new values.
        Schema::table('giveaway_entries', function (Blueprint $t) {
            foreach (['terms_hash', 'terms_accepted_at', 'score'] as $c) {
                if (Schema::hasColumn('giveaway_entries', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
        Schema::table('giveaway_campaigns', function (Blueprint $t) {
            foreach (['milestones', 'threshold_score', 'terms'] as $c) {
                if (Schema::hasColumn('giveaway_campaigns', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
