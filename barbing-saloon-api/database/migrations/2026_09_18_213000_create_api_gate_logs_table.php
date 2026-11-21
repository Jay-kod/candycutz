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
        Schema::create('api_gate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path', 255);
            $table->string('client_type', 20)->default('web'); // web, mobile, webhook, unknown
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('duration_ms'); // Total response latency in ms
            $table->unsignedInteger('query_count')->default(0); // DB queries executed during request
            $table->unsignedInteger('query_duration_ms')->default(0); // DB query time in ms
            $table->unsignedBigInteger('memory_bytes')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_role', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_slow')->default(false); // > 500ms
            $table->boolean('budget_exceeded')->default(false); // > 25 queries
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at', 'status_code'], 'gate_logs_created_status_idx');
            $table->index(['client_type', 'created_at'], 'gate_logs_client_created_idx');
            $table->index(['is_slow', 'created_at'], 'gate_logs_slow_created_idx');
            $table->index(['budget_exceeded', 'created_at'], 'gate_logs_budget_created_idx');
            $table->index(['path', 'method'], 'gate_logs_path_method_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_gate_logs');
    }
};
