<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_delivery_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->unsignedInteger('total_tokens')->default(0);
            $table->unsignedInteger('valid_tokens')->default(0);
            $table->unsignedInteger('expired_tokens')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('failure_reasons')->nullable(); // reason => count
            $table->unsignedInteger('active_users_with_token')->default(0);
            $table->timestamps();

            $table->unique('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_delivery_stats');
    }
};