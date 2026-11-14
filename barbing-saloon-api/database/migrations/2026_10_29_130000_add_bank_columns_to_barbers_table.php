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
        Schema::table('barbers', function (Blueprint $table) {
            if (! Schema::hasColumn('barbers', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('bio');
            }
            if (! Schema::hasColumn('barbers', 'account_name')) {
                $table->string('account_name', 100)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('barbers', 'account_number')) {
                $table->string('account_number', 30)->nullable()->after('account_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            $columnsToDrop = array_filter(
                ['bank_name', 'account_name', 'account_number'],
                fn ($col) => Schema::hasColumn('barbers', $col)
            );
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

