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
        if (! Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->enum('transaction_type', ['authorization', 'capture', 'refund', 'void']);
                $table->string('gateway', 50)->default('manual_transfer');
                $table->string('gateway_event_id', 150)->nullable()->unique();
                $table->unsignedBigInteger('amount');
                $table->string('currency', 3)->default('NGN');
                $table->unsignedBigInteger('amount_minor')->nullable();
                $table->string('idempotency_key', 200)->nullable()->unique();
                $table->json('raw_payload')->nullable();
                $table->string('status', 50);
                $table->timestamp('created_at')->useCurrent();
                $table->softDeletes();

                $table->index(['payment_id', 'transaction_type']);
            });
        } else {
            Schema::table('payment_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_transactions', 'currency')) {
                    $table->string('currency', 3)->default('NGN')->after('amount');
                }
                if (! Schema::hasColumn('payment_transactions', 'amount_minor')) {
                    $table->unsignedBigInteger('amount_minor')->nullable()->after('amount');
                }
                if (! Schema::hasColumn('payment_transactions', 'idempotency_key')) {
                    $table->string('idempotency_key', 200)->nullable()->unique()->after('amount_minor');
                }
                if (! Schema::hasColumn('payment_transactions', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

