<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (! Schema::hasColumn('appointments', 'reminder_30m_sent_at')) {
                    $table->timestamp('reminder_30m_sent_at')->nullable()->after('draft_step');
                    $table->index(['status', 'appointment_date', 'reminder_30m_sent_at'], 'appointments_status_date_reminder30m_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'reminder_30m_sent_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('appointments_status_date_reminder30m_idx');
                $table->dropColumn('reminder_30m_sent_at');
            });
        }
    }
};
