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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
            $table->timestamp('receipt_uploaded_at')->nullable()->after('receipt_url');
            $table->timestamp('verified_at')->nullable()->after('receipt_uploaded_at');
            $table->foreignId('verified_by_user_id')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->timestamp('sla_expires_at')->nullable()->after('verified_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['verified_by_user_id']);
            $table->dropColumn([
                'receipt_uploaded_at',
                'verified_at',
                'verified_by_user_id',
                'sla_expires_at',
            ]);
        });
    }
};
