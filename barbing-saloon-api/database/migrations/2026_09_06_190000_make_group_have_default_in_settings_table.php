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
        if (Schema::hasTable('settings')) {
            DB::statement("ALTER TABLE `settings` MODIFY COLUMN `group` VARCHAR(255) NOT NULL DEFAULT 'general'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            DB::statement("ALTER TABLE `settings` MODIFY COLUMN `group` VARCHAR(255) NOT NULL");
        }
    }
};
