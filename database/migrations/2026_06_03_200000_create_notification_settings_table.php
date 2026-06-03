<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('channel');        // email, sms, whatsapp, in_app
            $table->string('event_type');     // merchant_registration, customer_registration, points_earned, blast, etc
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable(); // API keys, templates, webhook URLs per channel
            $table->timestamps();

            $table->unique(['channel', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
