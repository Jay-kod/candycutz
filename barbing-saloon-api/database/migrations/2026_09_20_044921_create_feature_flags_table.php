<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'new_booking_flow', 'dark_mode'
            $table->string('name'); // Human readable name
            $table->text('description')->nullable();
            $table->json('enabled_for')->nullable(); // ["web"], ["app"], ["web","app"]
            $table->unsignedInteger('rollout_percentage')->default(100); // 0-100
            $table->json('conditions')->nullable(); // { "min_app_version": "1.2.0", "roles": ["customer"] }
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};