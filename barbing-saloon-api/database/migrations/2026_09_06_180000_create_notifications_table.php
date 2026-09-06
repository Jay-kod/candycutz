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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->string('recipient_type', 64)->default('customer');
                $table->unsignedBigInteger('recipient_id')->nullable();
                $table->string('type', 64)->default('system');
                $table->string('title', 255);
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->unsignedBigInteger('related_entity_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();

                $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['recipient_type', 'recipient_id']);
                $table->index('recipient_id');
                $table->index('created_at');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'notification_preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('notification_preferences')->nullable()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'notification_preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notification_preferences');
            });
        }
    }
};
