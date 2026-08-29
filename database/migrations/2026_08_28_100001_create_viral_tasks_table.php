<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viral_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('platform')->default('any'); // facebook, instagram, tiktok, twitter, any
            $table->enum('task_type', ['share_post', 'follow', 'refer_friend', 'visit_link', 'custom'])->default('share_post');
            $table->unsignedInteger('points_reward')->default(100);
            $table->boolean('requires_screenshot')->default(true);
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->unsignedInteger('max_completions')->nullable(); // NULL = unlimited
            $table->unsignedInteger('current_completions')->default(0);
            $table->unsignedInteger('total_points_spent')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viral_tasks');
    }
};
