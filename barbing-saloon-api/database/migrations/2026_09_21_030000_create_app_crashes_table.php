<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_crashes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_message');
            $table->longText('stack_trace')->nullable();
            $table->text('component_stack')->nullable();
            $table->string('app_version', 32)->nullable()->index();
            $table->string('platform', 20)->default('unknown')->index();
            $table->json('device_info')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();

            $table->index(['platform', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_crashes');
    }
};
