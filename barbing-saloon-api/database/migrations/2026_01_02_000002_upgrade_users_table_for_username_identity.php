<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('id');
            $table->string('real_name')->nullable()->after('username');
            $table->string('auth_provider', 50)->nullable()->default('local')->after('role');
            $table->string('provider_id', 150)->nullable()->after('auth_provider');
            $table->enum('status', ['active', 'username_pending', 'deactivated', 'suspended'])->default('active')->after('provider_id');
            $table->timestamp('last_username_change_at')->nullable()->after('status');
            $table->timestamp('deactivated_at')->nullable()->after('last_username_change_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'real_name',
                'auth_provider',
                'provider_id',
                'status',
                'last_username_change_at',
                'deactivated_at',
            ]);
        });
    }
};
