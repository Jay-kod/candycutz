<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // 1. Expand status column to include checked_in and in_progress
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending'");
        } elseif ($driver !== 'pgsql') {
            // For SQLite and others, ensure the status column allows new string values
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status', 30)->default('pending')->change();
            });
        }

        // 2. Add active_slot_key generated column and unique index as DB backstop against double booking
        if (! Schema::hasColumn('appointments', 'active_slot_key')) {
            if ($driver === 'pgsql') {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('active_slot_key', 64)->nullable();
                });
                DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS appointments_active_slot_unique ON appointments (barber_id, appointment_date, appointment_time) WHERE status IN ('pending', 'confirmed', 'checked_in', 'in_progress')");
            } else {
                Schema::table('appointments', function (Blueprint $table) use ($driver) {
                    if ($driver === 'sqlite') {
                        $expression = "CASE WHEN status IN ('pending', 'confirmed', 'checked_in', 'in_progress') THEN (barber_id || '-' || appointment_date || '-' || appointment_time) ELSE NULL END";
                    } else {
                        $expression = "IF(status IN ('pending', 'confirmed', 'checked_in', 'in_progress'), CONCAT(barber_id, '-', appointment_date, '-', appointment_time), NULL)";
                    }

                    $table->string('active_slot_key', 64)->nullable()->virtualAs($expression);
                    $table->unique('active_slot_key', 'appointments_active_slot_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS appointments_active_slot_unique');
            if (Schema::hasColumn('appointments', 'active_slot_key')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropColumn('active_slot_key');
                });
            }
        } else {
            if (Schema::hasColumn('appointments', 'active_slot_key')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropUnique('appointments_active_slot_unique');
                    $table->dropColumn('active_slot_key');
                });
            }
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending'");
        }
    }
};
