<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'no_show_count')) {
                $table->unsignedInteger('no_show_count')->default(0)->after('is_active');
            }
            if (! Schema::hasColumn('users', 'last_no_show_at')) {
                $table->timestamp('last_no_show_at')->nullable()->after('no_show_count');
            }
            if (! Schema::hasColumn('users', 'requires_deposit')) {
                $table->boolean('requires_deposit')->default(false)->after('last_no_show_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['no_show_count', 'last_no_show_at', 'requires_deposit'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
