<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            if (!Schema::hasColumn('barbers', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('user_id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('barbers', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('is_featured');
            }
            if (!Schema::hasColumn('barbers', 'is_home_service_ready')) {
                $table->boolean('is_home_service_ready')->default(true);
            }
        });

        Schema::table('service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_categories', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('display_order');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('services', 'home_service_allowed')) {
                $table->boolean('home_service_allowed')->default(true)->after('duration_minutes');
            }
            if (!Schema::hasColumn('services', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
        });

        if (!Schema::hasTable('barber_services')) {
            Schema::create('barber_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barber_id')->constrained('barbers')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->decimal('custom_price', 10, 2)->nullable();
                $table->integer('custom_duration')->nullable();
                $table->boolean('is_offered')->default(true);
                $table->timestamps();

                $table->unique(['barber_id', 'service_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_services');
    }
};
