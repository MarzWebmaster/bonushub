<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Giveaway campaigns — merchant creates giveaway events
        Schema::create('giveaway_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('prize_description'); // e.g. "Free Product X", "RM100 Voucher"
            $table->integer('prize_value')->default(0); // estimated value in points
            $table->integer('max_entries')->nullable(); // null = unlimited
            $table->integer('winner_count')->default(1); // how many winners
            $table->enum('status', ['draft', 'active', 'ended', 'cancelled'])->default('draft');
            $table->enum('selection_method', ['manual', 'random', 'top_referrers'])->default('manual');
            $table->enum('entry_method', ['referral', 'task', 'purchase', 'manual'])->default('referral');
            $table->integer('entries_per_referral')->default(1); // entries earned per successful referral
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('winners_announced_at')->nullable();
            $table->json('metadata')->nullable(); // extra config
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
        });

        // Giveaway entries — each entry = a chance to win
        Schema::create('giveaway_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giveaway_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('entry_count')->default(1); // how many entries this customer has
            $table->enum('source', ['referral', 'task', 'purchase', 'manual'])->default('referral');
            $table->string('source_reference')->nullable(); // e.g. referral code, task ID
            $table->boolean('is_winner')->default(false);
            $table->string('prize_won')->nullable(); // specific prize description
            $table->timestamp('won_at')->nullable();
            $table->timestamps();

            $table->unique(['giveaway_campaign_id', 'customer_id'], 'giveaway_unique_entry');
            $table->index(['giveaway_campaign_id', 'is_winner']);
        });

        // Giveaway winners — announced winners with details
        Schema::create('giveaway_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giveaway_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('giveaway_entry_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(1); // 1st winner, 2nd winner, etc.
            $table->string('prize_description');
            $table->string('status', 20)->default('pending'); // pending, claimed, expired
            $table->text('notes')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giveaway_winners');
        Schema::dropIfExists('giveaway_entries');
        Schema::dropIfExists('giveaway_campaigns');
    }
};