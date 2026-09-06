<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('CandyCutz');
            $table->string('legal_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->default(8.84710000);
            $table->decimal('longitude', 11, 8)->default(7.87360000);
            $table->string('phone', 30);
            $table->string('email');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('boundary_polygon')->nullable();
            $table->decimal('radius_km', 6, 2)->default(15.00);
            $table->decimal('base_travel_fee', 10, 2)->default(2000.00);
            $table->decimal('per_km_fee', 10, 2)->default(150.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_zones');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('businesses');
    }
};
