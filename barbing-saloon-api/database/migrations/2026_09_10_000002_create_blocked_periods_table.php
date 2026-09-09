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
        if (!Schema::hasTable('blocked_periods')) {
            Schema::create('blocked_periods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barber_id')->constrained('barbers')->cascadeOnDelete();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->dateTime('start_datetime');
                $table->dateTime('end_datetime');
                $table->string('reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['barber_id', 'start_datetime', 'end_datetime']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_periods');
    }
};
