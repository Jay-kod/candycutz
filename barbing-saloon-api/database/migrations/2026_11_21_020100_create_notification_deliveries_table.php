<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->string('channel', 32); // in_app, push, email, sse
            $table->string('recipient_email')->nullable();
            $table->string('recipient_token', 500)->nullable();
            $table->string('status', 32)->default('pending'); // pending, processing, accepted, delivered, failed, suppressed, invalid_device
            $table->unsignedInteger('attempts')->default(0);
            $table->string('provider_reference')->nullable();
            $table->string('failure_code', 64)->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'channel']);
            $table->index(['notification_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
