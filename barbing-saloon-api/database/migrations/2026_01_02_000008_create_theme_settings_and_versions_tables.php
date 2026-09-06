<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('theme_name', 100)->default('CandyCutz Royal');
            $table->json('tokens_json');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });

        Schema::create('theme_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_setting_id')->constrained('theme_settings')->cascadeOnDelete();
            $table->integer('version_number');
            $table->json('tokens_json');
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['theme_setting_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_versions');
        Schema::dropIfExists('theme_settings');
    }
};
