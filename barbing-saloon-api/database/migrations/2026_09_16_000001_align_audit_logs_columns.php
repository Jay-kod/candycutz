<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('audit_logs', 'user_role')) {
                $table->string('user_role')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('audit_logs', 'module')) {
                $table->string('module')->nullable()->after('action');
            }
            if (! Schema::hasColumn('audit_logs', 'target_type')) {
                $table->string('target_type')->nullable()->after('module');
            }
            if (! Schema::hasColumn('audit_logs', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            }
            if (! Schema::hasColumn('audit_logs', 'old_value')) {
                $table->json('old_value')->nullable()->after('target_id');
            }
            if (! Schema::hasColumn('audit_logs', 'new_value')) {
                $table->json('new_value')->nullable()->after('old_value');
            }
            if (! Schema::hasColumn('audit_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            foreach (['user_role', 'module', 'target_type', 'target_id', 'old_value', 'new_value', 'updated_at'] as $column) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
