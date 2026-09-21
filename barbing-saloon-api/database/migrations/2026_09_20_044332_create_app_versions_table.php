<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->enum('platform', ['ios', 'android'])->index();
            $table->boolean('is_minimum')->default(false);
            $table->boolean('is_latest')->default(false);
            $table->boolean('force_update')->default(false);
            $table->text('release_notes')->nullable();
            $table->string('store_url')->nullable(); // App Store / Play Store URL
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['version', 'platform']);
            $table->index(['platform', 'is_minimum']);
            $table->index(['platform', 'is_latest']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};