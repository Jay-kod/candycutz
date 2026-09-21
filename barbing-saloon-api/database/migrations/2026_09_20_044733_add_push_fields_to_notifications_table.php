<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('push_sent_at')->nullable()->after('is_read');
            $table->timestamp('push_delivered_at')->nullable()->after('push_sent_at');
            $table->timestamp('push_failed_at')->nullable()->after('push_delivered_at');
            $table->text('push_failure_reason')->nullable()->after('push_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['push_sent_at', 'push_delivered_at', 'push_failed_at', 'push_failure_reason']);
        });
    }
};