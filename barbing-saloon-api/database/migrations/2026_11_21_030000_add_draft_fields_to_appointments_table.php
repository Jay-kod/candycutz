<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // 1. Expand status ENUM in MySQL to include 'draft'
        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('draft', 'pending', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'draft'");
            } catch (\Throwable) {
                // In case column was already converted to string or updated
            }
        }

        // 2. Add draft columns and high-throughput composite indexes
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'draft_expires_at')) {
                $table->timestamp('draft_expires_at')->nullable()->index()->after('payment_due_at');
            }
            if (! Schema::hasColumn('appointments', 'draft_step')) {
                $table->string('draft_step', 40)->nullable()->default('payment')->after('draft_expires_at');
            }
        });

        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index(['status', 'draft_expires_at'], 'appointments_status_draft_expires_idx');
            });
        } catch (\Throwable) {
            // Index already exists or driver unsupported
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('appointments_status_draft_expires_idx');
            });
        } catch (\Throwable) {
        }

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'draft_expires_at')) {
                $table->dropIndex(['draft_expires_at']);
                $table->dropColumn('draft_expires_at');
            }
            if (Schema::hasColumn('appointments', 'draft_step')) {
                $table->dropColumn('draft_step');
            }
        });
    }
};
