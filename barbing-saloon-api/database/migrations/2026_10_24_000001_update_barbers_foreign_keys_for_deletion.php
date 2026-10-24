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
        // 1. appointments: make barber_id nullable and set null on delete so historical records are kept
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign('appointments_barber_id_foreign');
            $table->unsignedBigInteger('barber_id')->nullable()->change();
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers')
                ->nullOnDelete();
        });

        // 2. gallery: set null on delete so gallery images remain part of the shop portfolio
        Schema::table('gallery', function (Blueprint $table) {
            $table->dropForeign('gallery_barber_id_foreign');
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers')
                ->nullOnDelete();
        });

        // 3. testimonials: set null on delete so customer reviews remain with the shop
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropForeign('testimonials_barber_id_foreign');
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers')
                ->nullOnDelete();
        });

        // 4. working_hours: cascade on delete when a barber is removed
        Schema::table('working_hours', function (Blueprint $table) {
            $table->dropForeign('working_hours_barber_id_foreign');
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_hours', function (Blueprint $table) {
            $table->dropForeign(['barber_id']);
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropForeign(['barber_id']);
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers');
        });

        Schema::table('gallery', function (Blueprint $table) {
            $table->dropForeign(['barber_id']);
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['barber_id']);
            $table->foreign('barber_id')
                ->references('id')
                ->on('barbers');
        });
    }
};

