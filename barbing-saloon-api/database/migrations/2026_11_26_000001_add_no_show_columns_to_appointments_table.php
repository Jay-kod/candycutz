<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'no_show_marked_at')) {
                $table->timestamp('no_show_marked_at')->nullable()->after('reminder_30m_sent_at');
            }
            if (! Schema::hasColumn('appointments', 'no_show_marked_by')) {
                $table->foreignId('no_show_marked_by')->nullable()->after('no_show_marked_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('appointments', 'no_show_auto')) {
                $table->boolean('no_show_auto')->default(false)->after('no_show_marked_by');
            }
            if (! Schema::hasColumn('appointments', 'no_show_warning_sent_at')) {
                $table->timestamp('no_show_warning_sent_at')->nullable()->after('no_show_auto');
            }
            if (! Schema::hasColumn('appointments', 'grace_extended_until')) {
                $table->timestamp('grace_extended_until')->nullable()->after('no_show_warning_sent_at');
            }
            if (! Schema::hasColumn('appointments', 'grace_extension_count')) {
                $table->unsignedTinyInteger('grace_extension_count')->default(0)->after('grace_extended_until');
            }
            if (! Schema::hasColumn('appointments', 'grace_extension_reason')) {
                $table->string('grace_extension_reason', 500)->nullable()->after('grace_extension_count');
            }
            if (! Schema::hasColumn('appointments', 'deposit_forfeited')) {
                $table->boolean('deposit_forfeited')->default(false)->after('grace_extension_reason');
            }
            if (! Schema::hasColumn('appointments', 'deposit_forfeited_at')) {
                $table->timestamp('deposit_forfeited_at')->nullable()->after('deposit_forfeited');
            }
            if (! Schema::hasColumn('appointments', 'deposit_forfeited_by')) {
                $table->foreignId('deposit_forfeited_by')->nullable()->after('deposit_forfeited_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['no_show_marked_by', 'deposit_forfeited_by'] as $fk) {
                if (Schema::hasColumn('appointments', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }

            foreach ([
                'no_show_marked_at',
                'no_show_auto',
                'no_show_warning_sent_at',
                'grace_extended_until',
                'grace_extension_count',
                'grace_extension_reason',
                'deposit_forfeited',
                'deposit_forfeited_at',
            ] as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
