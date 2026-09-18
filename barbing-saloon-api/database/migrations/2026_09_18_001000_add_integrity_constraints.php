<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add soft deletes to payments, payment_transactions, and appointment_items
        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('appointment_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 2. Add composite indexes on appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['barber_id', 'appointment_date', 'status'], 'appointments_barber_date_status_idx');
            $table->index(['customer_id', 'status'], 'appointments_customer_status_idx');
        });

        // 3. Add composite index on payments
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['appointment_id', 'status'], 'payments_appointment_status_idx');
        });

        // In MariaDB 10.4, functional indexes are not supported. We create a virtual column and index it.
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('active_status', 10)
                  ->virtualAs("CASE WHEN status NOT IN ('cancelled', 'no_show') THEN 'active' ELSE NULL END")
                  ->nullable();
            
            $table->unique(['barber_id', 'appointment_date', 'appointment_time', 'active_status'], 'appointments_barber_datetime_unique');
        });

        // 5. Update foreign key for appointments -> services to be restrictOnDelete explicitly
        // Since it's already restrict by default, we just drop and recreate to be explicit as per ARCHITECTURE.md
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->foreign('service_id')->references('id')->on('services');
            
            $table->dropUnique('appointments_barber_datetime_unique');
            $table->dropColumn('active_status');
            
            $table->dropIndex('appointments_barber_date_status_idx');
            $table->dropIndex('appointments_customer_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_appointment_status_idx');
        });

        Schema::table('appointment_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
