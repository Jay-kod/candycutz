<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('category', 32)->default('system')->after('type');
            $table->json('data')->nullable()->after('message');
            $table->string('action_url', 255)->nullable()->after('data');
            $table->string('priority', 16)->default('normal')->after('action_url');
            $table->string('idempotency_key', 64)->nullable()->unique()->after('priority');
            $table->timestamp('expires_at')->nullable()->after('push_failure_reason');

            $table->index(['category', 'created_at']);
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['category', 'created_at']);
            $table->dropIndex(['priority']);
            $table->dropColumn([
                'category',
                'data',
                'action_url',
                'priority',
                'idempotency_key',
                'expires_at',
            ]);
        });
    }
};
