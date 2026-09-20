<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blog_reactions')) {
            Schema::create('blog_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('blog_posts')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->string('reaction_type', 32)->default('love');
                $table->timestamps();

                $table->unique(['post_id', 'customer_id', 'reaction_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_reactions');
    }
};
