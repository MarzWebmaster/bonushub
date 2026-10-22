<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Giveaway claim deadline + replacement winners (PRD v1.2 6.3.3). Additive. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giveaway_campaigns', function (Blueprint $t) {
            if (!Schema::hasColumn('giveaway_campaigns', 'claim_days')) {
                $t->unsignedSmallInteger('claim_days')->default(7);
            }
        });
        Schema::table('giveaway_winners', function (Blueprint $t) {
            if (!Schema::hasColumn('giveaway_winners', 'claim_deadline_at')) {
                $t->timestamp('claim_deadline_at')->nullable();
            }
            if (!Schema::hasColumn('giveaway_winners', 'replaces_winner_id')) {
                $t->unsignedBigInteger('replaces_winner_id')->nullable();
            }
            if (!Schema::hasColumn('giveaway_winners', 'notified_at')) {
                $t->timestamp('notified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('giveaway_winners', function (Blueprint $t) {
            foreach (['notified_at', 'replaces_winner_id', 'claim_deadline_at'] as $c) {
                if (Schema::hasColumn('giveaway_winners', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
        Schema::table('giveaway_campaigns', function (Blueprint $t) {
            if (Schema::hasColumn('giveaway_campaigns', 'claim_days')) {
                $t->dropColumn('claim_days');
            }
        });
    }
};
