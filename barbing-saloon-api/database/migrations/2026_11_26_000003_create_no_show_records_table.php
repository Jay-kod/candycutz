<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('no_show_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at');
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 500)->nullable();
            $table->boolean('was_automatic')->default(false);
            $table->boolean('deposit_forfeited')->default(false);
            $table->unsignedBigInteger('deposit_forfeited_minor')->default(0);
            $table->timestamp('deposit_forfeited_at')->nullable();
            $table->foreignId('deposit_forfeited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('grace_extended')->default(false);
            $table->string('grace_reason', 500)->nullable();
            $table->boolean('appealed')->default(false);
            $table->text('appeal_reason')->nullable();
            $table->timestamp('appealed_at')->nullable();
            $table->string('appeal_outcome', 30)->nullable(); // pending, approved, rejected
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->unique('appointment_id');
            $table->index(['customer_id', 'marked_at']);
            $table->index('appeal_outcome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('no_show_records');
    }
};
