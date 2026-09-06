<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 10)->default('NGN');
                $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
                $table->string('payment_method', 50)->default('stripe');
                $table->string('stripe_payment_intent_id', 150)->nullable()->index();
                $table->string('stripe_charge_id', 150)->nullable();
                $table->string('transaction_ref', 100)->unique();
                $table->string('receipt_url', 255)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['appointment_id', 'status']);
            });
        }

        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->enum('transaction_type', ['authorization', 'capture', 'refund', 'void']);
                $table->string('gateway', 50)->default('stripe');
                $table->string('gateway_event_id', 150)->unique()->nullable();
                $table->decimal('amount', 10, 2);
                $table->json('raw_payload')->nullable();
                $table->string('status', 50);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['payment_id', 'transaction_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
    }
};
