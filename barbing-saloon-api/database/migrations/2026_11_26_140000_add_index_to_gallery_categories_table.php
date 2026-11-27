<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gallery_categories') && ! Schema::hasIndex('gallery_categories', 'gallery_categories_active_order_idx')) {
            Schema::table('gallery_categories', function (Blueprint $table) {
                $table->index(['is_active', 'display_order'], 'gallery_categories_active_order_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gallery_categories') && Schema::hasIndex('gallery_categories', 'gallery_categories_active_order_idx')) {
            Schema::table('gallery_categories', function (Blueprint $table) {
                $table->dropIndex('gallery_categories_active_order_idx');
            });
        }
    }
};
