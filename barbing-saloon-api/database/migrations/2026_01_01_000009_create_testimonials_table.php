<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users');
            $table->string('client_name');
            $table->string('client_avatar')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->foreignId('service_id')->nullable()->constrained('services');
            $table->foreignId('barber_id')->nullable()->constrained('barbers');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};