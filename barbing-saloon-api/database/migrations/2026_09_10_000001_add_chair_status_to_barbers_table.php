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
        if (Schema::hasTable('barbers')) {
            Schema::table('barbers', function (Blueprint $table) {
                if (!Schema::hasColumn('barbers', 'chair_status')) {
                    $table->string('chair_status', 20)->default('free');
                }
                if (!Schema::hasColumn('barbers', 'is_available')) {
                    $table->boolean('is_available')->default(true);
                }
                if (!Schema::hasColumn('barbers', 'rating')) {
                    $table->decimal('rating', 3, 1)->default(5.0);
                }
                if (!Schema::hasColumn('barbers', 'experience_years')) {
                    $table->unsignedInteger('experience_years')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('barbers')) {
            Schema::table('barbers', function (Blueprint $table) {
                if (Schema::hasColumn('barbers', 'chair_status')) {
                    $table->dropColumn('chair_status');
                }
            });
        }
    }
};
