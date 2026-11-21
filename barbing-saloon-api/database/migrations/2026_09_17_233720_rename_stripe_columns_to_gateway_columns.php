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
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('stripe_payment_intent_id', 'gateway_reference');
            $table->renameColumn('stripe_charge_id', 'gateway_charge_id');
        });

        // The default values need to be modified.
        // It's tricky to alter defaults across different DBs using just Schema::table easily without doctrine/dbal.
        // Instead we update existing records, new ones will just provide 'paystack' or 'manual_transfer'
        DB::table('payments')->where('payment_method', 'stripe')->update(['payment_method' => 'paystack']);
        DB::table('payment_transactions')->where('gateway', 'stripe')->update(['gateway' => 'paystack']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('gateway_reference', 'stripe_payment_intent_id');
            $table->renameColumn('gateway_charge_id', 'stripe_charge_id');
        });
    }
};
