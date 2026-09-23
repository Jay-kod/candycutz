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
        if (DB::getDriverName() === 'mysql') {
            // Convert payments.amount from decimal(10,2) to bigInteger (kobo)
            if (Schema::hasTable('payments')) {
                DB::statement('ALTER TABLE `payments` MODIFY COLUMN `amount` BIGINT UNSIGNED NOT NULL');
                // Backfill: multiply existing values by 100
                DB::statement('UPDATE `payments` SET `amount` = ROUND(`amount` * 100)');
            }

            // Convert services.price from decimal(8,2) to bigInteger (kobo)
            if (Schema::hasTable('services')) {
                DB::statement('ALTER TABLE `services` MODIFY COLUMN `price` BIGINT UNSIGNED NOT NULL');
                DB::statement('UPDATE `services` SET `price` = ROUND(`price` * 100)');
            }

            // Convert appointments money columns
            if (Schema::hasTable('appointments')) {
                $moneyColumns = [
                    'total_amount',
                    'travel_fee',
                    'tip_amount',
                    'discount_amount',
                    'grand_total',
                    'total_price',
                    'deposit_amount',
                ];

                foreach ($moneyColumns as $column) {
                    if (Schema::hasColumn('appointments', $column)) {
                        DB::statement("ALTER TABLE `appointments` MODIFY COLUMN `$column` BIGINT UNSIGNED NOT NULL DEFAULT 0");
                        DB::statement("UPDATE `appointments` SET `$column` = ROUND(`$column` * 100)");
                    }
                }
            }

            // Convert payment_transactions.amount
            if (Schema::hasTable('payment_transactions')) {
                DB::statement('ALTER TABLE `payment_transactions` MODIFY COLUMN `amount` BIGINT UNSIGNED NOT NULL');
                DB::statement('UPDATE `payment_transactions` SET `amount` = ROUND(`amount` * 100)');
            }
        }

        // Add currency column to payment_transactions if not exists
        if (Schema::hasTable('payment_transactions') && !Schema::hasColumn('payment_transactions', 'currency')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->string('currency', 3)->default('NGN')->after('amount');
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            DB::table('payment_transactions')
                ->whereNull('currency')
                ->orWhere('currency', '')
                ->update(['currency' => 'NGN']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Convert back to decimal - WARNING: this may lose precision for kobo values
            if (Schema::hasTable('payments')) {
                DB::statement('ALTER TABLE `payments` MODIFY COLUMN `amount` DECIMAL(10,2) UNSIGNED NOT NULL');
                DB::statement('UPDATE `payments` SET `amount` = `amount` / 100');
            }

            if (Schema::hasTable('services')) {
                DB::statement('ALTER TABLE `services` MODIFY COLUMN `price` DECIMAL(8,2) UNSIGNED NOT NULL');
                DB::statement('UPDATE `services` SET `price` = `price` / 100');
            }

            if (Schema::hasTable('appointments')) {
                $moneyColumns = [
                    'total_amount',
                    'travel_fee',
                    'tip_amount',
                    'discount_amount',
                    'grand_total',
                    'total_price',
                    'deposit_amount',
                ];

                foreach ($moneyColumns as $column) {
                    if (Schema::hasColumn('appointments', $column)) {
                        DB::statement("ALTER TABLE `appointments` MODIFY COLUMN `$column` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0");
                        DB::statement("UPDATE `appointments` SET `$column` = `$column` / 100");
                    }
                }
            }

            if (Schema::hasTable('payment_transactions')) {
                DB::statement('ALTER TABLE `payment_transactions` MODIFY COLUMN `amount` DECIMAL(10,2) UNSIGNED NOT NULL');
                DB::statement('UPDATE `payment_transactions` SET `amount` = `amount` / 100');
            }
        }

        if (Schema::hasTable('payment_transactions') && Schema::hasColumn('payment_transactions', 'currency')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};