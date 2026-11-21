<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_groups', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->string('error_code', 64)->index();
            $table->string('category', 64)->index();
            $table->string('severity', 32)->index();
            $table->string('exception_class', 255);
            $table->text('sample_message');
            $table->string('source', 32)->default('api')->index();
            $table->string('status', 32)->default('open')->index();
            $table->unsignedBigInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->string('last_request_id', 64)->nullable();
            $table->unsignedInteger('affected_users_count')->default(0);
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamp('regressed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['last_seen_at', 'status']);
        });

        Schema::create('error_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('error_groups')->cascadeOnDelete();
            $table->string('request_id', 64)->index();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->smallInteger('http_status')->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('route_uri', 255)->nullable()->index();
            $table->text('url_redacted')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('role', 32)->nullable();
            $table->string('client', 32)->nullable()->index();
            $table->string('app_version', 32)->nullable();
            $table->string('device_label', 255)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('exception_class', 255);
            $table->text('message');
            $table->string('file', 255)->nullable();
            $table->integer('line')->nullable();
            $table->mediumText('trace')->nullable();
            $table->json('context')->nullable();
            $table->text('previous')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->decimal('memory_mb', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['group_id', 'occurred_at']);
        });

        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 64)->index();
            $table->string('method', 10);
            $table->string('route_uri', 255)->index();
            $table->smallInteger('status')->index();
            $table->integer('duration_ms');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('client', 32)->nullable();
            $table->string('app_version', 32)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('error_events');
        Schema::dropIfExists('error_groups');
    }
};
