<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gallery_categories')) {
            Schema::create('gallery_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Convert gallery.category column from enum to string so dynamic categories are supported
        if (Schema::hasTable('gallery') && Schema::hasColumn('gallery', 'category')) {
            Schema::table('gallery', function (Blueprint $table) {
                $table->string('category', 100)->change();
            });
        }

        // Seed default categories if empty
        if (DB::table('gallery_categories')->count() === 0) {
            $now = now();
            DB::table('gallery_categories')->insert([
                [
                    'name' => 'Haircuts',
                    'slug' => 'haircut',
                    'icon' => 'scissors',
                    'description' => 'Precision fades, tapers, and classic haircuts.',
                    'display_order' => 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Beard & Shave',
                    'slug' => 'beard',
                    'icon' => 'sparkles',
                    'description' => 'Beard sculpting, hot towel shaves, and grooming.',
                    'display_order' => 2,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Combos & VIP',
                    'slug' => 'combo',
                    'icon' => 'crown',
                    'description' => 'Complete hair and beard styling packages.',
                    'display_order' => 3,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Transformations',
                    'slug' => 'before_after',
                    'icon' => 'flame',
                    'description' => 'Dramatic before and after transformations.',
                    'display_order' => 4,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Shop & Vibe',
                    'slug' => 'shop',
                    'icon' => 'camera',
                    'description' => 'Lounge interior, equipment, and shop atmosphere.',
                    'display_order' => 5,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_categories');
    }
};
