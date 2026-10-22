<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Referral Campaign (campaign links): clicks vs unique visitors.
 *  - campaign_links.visits stays the number of CLICKS; unique_visits is the number of distinct visitors.
 *  - campaign_link_visitors keeps one row per (link, visitor) where visitor = salted SHA-256 of IP + user agent (no raw IP).
 * Additive. Existing links get unique_visits = visits (history has no visitor data: that is the best lower bound we can state).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_links', function (Blueprint $t) {
            if (!Schema::hasColumn('campaign_links', 'unique_visits')) {
                $t->unsignedInteger('unique_visits')->default(0);
            }
        });

        if (!Schema::hasTable('campaign_link_visitors')) {
            Schema::create('campaign_link_visitors', function (Blueprint $t) {
                $t->id();
                $t->foreignId('campaign_link_id')->constrained('campaign_links')->cascadeOnDelete();
                $t->char('visitor_hash', 64);
                $t->unsignedInteger('hits')->default(1);
                $t->timestamp('first_seen_at')->nullable();
                $t->timestamp('last_seen_at')->nullable();
                $t->unique(['campaign_link_id', 'visitor_hash']);
            });
        }

        DB::table('campaign_links')->where('unique_visits', 0)->where('visits', '>', 0)->update(['unique_visits' => DB::raw('visits')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_link_visitors');
        Schema::table('campaign_links', function (Blueprint $t) {
            if (Schema::hasColumn('campaign_links', 'unique_visits')) {
                $t->dropColumn('unique_visits');
            }
        });
    }
};
