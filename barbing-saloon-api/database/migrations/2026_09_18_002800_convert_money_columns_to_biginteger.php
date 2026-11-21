<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For existing data (convert Decimal to Kobo, which is * 100)
        // Since we want this to be seamless, we run a query to update first.
        DB::table('services')->update(['price' => DB::raw('price * 100')]);
        DB::table('service_zones')->update([
            'base_travel_fee' => DB::raw('base_travel_fee * 100'),
            'per_km_fee' => DB::raw('per_km_fee * 100'),
        ]);
        DB::table('barber_services')->whereNotNull('custom_price')->update(['custom_price' => DB::raw('custom_price * 100')]);
        DB::table('appointments')->update([
            'total_amount' => DB::raw('total_amount * 100'),
            'travel_fee' => DB::raw('travel_fee * 100'),
            'tip_amount' => DB::raw('tip_amount * 100'),
            'discount_amount' => DB::raw('discount_amount * 100'),
            'grand_total' => DB::raw('grand_total * 100'),
            'total_price' => DB::raw('total_price * 100'),
            'deposit_amount' => DB::raw('deposit_amount * 100'),
        ]);
        DB::table('appointment_items')->update(['price' => DB::raw('price * 100')]);
        DB::table('payments')->update(['amount' => DB::raw('amount * 100')]);
        DB::table('payment_transactions')->update(['amount' => DB::raw('amount * 100')]);

        // Change column types to bigInteger
        Schema::table('services', function (Blueprint $table) {
            $table->bigInteger('price')->change();
        });

        Schema::table('service_zones', function (Blueprint $table) {
            $table->bigInteger('base_travel_fee')->default(200000)->change();
            $table->bigInteger('per_km_fee')->default(15000)->change();
        });

        Schema::table('barber_services', function (Blueprint $table) {
            $table->bigInteger('custom_price')->nullable()->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->bigInteger('total_amount')->default(0)->change();
            $table->bigInteger('travel_fee')->default(0)->change();
            $table->bigInteger('tip_amount')->default(0)->change();
            $table->bigInteger('discount_amount')->default(0)->change();
            $table->bigInteger('grand_total')->default(0)->change();
            $table->bigInteger('total_price')->change();
            $table->bigInteger('deposit_amount')->default(0)->change();
        });

        Schema::table('appointment_items', function (Blueprint $table) {
            $table->bigInteger('price')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert columns back to decimal
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
        });

        Schema::table('service_zones', function (Blueprint $table) {
            $table->decimal('base_travel_fee', 10, 2)->default(2000.00)->change();
            $table->decimal('per_km_fee', 10, 2)->default(150.00)->change();
        });

        Schema::table('barber_services', function (Blueprint $table) {
            $table->decimal('custom_price', 10, 2)->nullable()->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('total_amount', 10, 2)->default(0.00)->change();
            $table->decimal('travel_fee', 10, 2)->default(0.00)->change();
            $table->decimal('tip_amount', 10, 2)->default(0.00)->change();
            $table->decimal('discount_amount', 10, 2)->default(0.00)->change();
            $table->decimal('grand_total', 10, 2)->default(0.00)->change();
            $table->decimal('total_price', 8, 2)->change();
            $table->decimal('deposit_amount', 8, 2)->default(0.00)->change();
        });

        Schema::table('appointment_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        // Revert data
        DB::table('services')->update(['price' => DB::raw('price / 100.0')]);
        DB::table('service_zones')->update([
            'base_travel_fee' => DB::raw('base_travel_fee / 100.0'),
            'per_km_fee' => DB::raw('per_km_fee / 100.0'),
        ]);
        DB::table('barber_services')->whereNotNull('custom_price')->update(['custom_price' => DB::raw('custom_price / 100.0')]);
        DB::table('appointments')->update([
            'total_amount' => DB::raw('total_amount / 100.0'),
            'travel_fee' => DB::raw('travel_fee / 100.0'),
            'tip_amount' => DB::raw('tip_amount / 100.0'),
            'discount_amount' => DB::raw('discount_amount / 100.0'),
            'grand_total' => DB::raw('grand_total / 100.0'),
            'total_price' => DB::raw('total_price / 100.0'),
            'deposit_amount' => DB::raw('deposit_amount / 100.0'),
        ]);
        DB::table('appointment_items')->update(['price' => DB::raw('price / 100.0')]);
        DB::table('payments')->update(['amount' => DB::raw('amount / 100.0')]);
        DB::table('payment_transactions')->update(['amount' => DB::raw('amount / 100.0')]);
    }
};
