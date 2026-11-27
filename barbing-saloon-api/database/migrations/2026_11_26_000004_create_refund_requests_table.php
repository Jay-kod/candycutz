<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32)->default('standard'); // standard, compassionate, no_show_dispute
            $table->string('reason_category', 32)->default('changed_mind'); // changed_mind, illness, emergency, barber_cancelled, other
            $table->text('reason_text')->nullable();
            $table->string('supporting_evidence', 500)->nullable();
            $table->unsignedBigInteger('refund_amount_minor')->default(0);
            $table->unsignedBigInteger('original_amount_minor')->default(0);
            $table->unsignedTinyInteger('refund_percentage')->default(0);
            $table->string('status', 32)->default('pending'); // pending, approved, rejected, processing, completed, failed
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->string('gateway_refund_ref', 191)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['appointment_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
