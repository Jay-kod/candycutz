<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users');
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email');
            $table->foreignId('barber_id')->constrained('barbers');
            $table->foreignId('service_id')->constrained('services');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('total_price', 8, 2);
            $table->boolean('deposit_paid')->default(false);
            $table->decimal('deposit_amount', 8, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['appointment_date', 'barber_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};