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
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('barber_id')
                ->nullable()
                ->after('category_id')
                ->constrained('barbers')
                ->nullOnDelete();

            $table->string('approval_status', 20)
                ->default('approved')
                ->after('is_active');

            $table->foreignId('approved_by')
                ->nullable()
                ->after('approval_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            $table->index(['approval_status', 'is_active'], 'services_approval_active_idx');
            $table->index(['barber_id', 'approval_status'], 'services_barber_approval_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_approval_active_idx');
            $table->dropIndex('services_barber_approval_idx');
            $table->dropForeign(['barber_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['barber_id', 'approval_status', 'approved_by', 'approved_at']);
        });
    }
};
