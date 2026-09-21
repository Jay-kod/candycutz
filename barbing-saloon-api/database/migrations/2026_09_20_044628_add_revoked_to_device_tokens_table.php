<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->boolean('is_revoked')->default(false)->after('last_seen_at');
            $table->timestamp('revoked_at')->nullable()->after('is_revoked');
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropForeign(['revoked_by_user_id']);
            $table->dropColumn(['is_revoked', 'revoked_at', 'revoked_by_user_id']);
        });
    }
};