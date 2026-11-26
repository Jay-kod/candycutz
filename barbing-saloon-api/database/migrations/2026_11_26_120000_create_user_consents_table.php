<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->uuid('consent_id')->nullable();
            $table->string('type', 20);
            $table->string('version', 40);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source', 40);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'version'], 'uq_user_consent');
            $table->index(['type', 'version'], 'idx_consent_type_version');
            $table->index('consent_id', 'idx_consent_consent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_consents');
    }
};
