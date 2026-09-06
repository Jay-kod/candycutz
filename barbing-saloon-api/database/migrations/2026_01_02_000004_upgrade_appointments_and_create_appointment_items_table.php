<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('booking_reference', 30)->nullable()->unique()->after('id');
            $table->foreignId('branch_id')->nullable()->after('booking_reference')->constrained('branches')->nullOnDelete();
            $table->enum('appointment_type', ['in_shop', 'home_service'])->default('in_shop')->after('service_id');
            $table->foreignId('service_zone_id')->nullable()->after('appointment_type')->constrained('service_zones')->nullOnDelete();
            $table->foreignId('customer_address_id')->nullable()->after('service_zone_id')->constrained('addresses')->nullOnDelete();
            $table->time('end_time')->nullable()->after('appointment_time');
            $table->integer('total_duration_minutes')->default(30)->after('end_time');
            $table->decimal('total_amount', 10, 2)->default(0.00)->after('total_duration_minutes');
            $table->decimal('travel_fee', 10, 2)->default(0.00)->after('total_amount');
            $table->decimal('tip_amount', 10, 2)->default(0.00)->after('travel_fee');
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('tip_amount');
            $table->decimal('grand_total', 10, 2)->default(0.00)->after('discount_amount');
            $table->text('cancellation_reason')->nullable()->after('notes');
        });

        Schema::create('appointment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(30);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_items');
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['service_zone_id']);
            $table->dropForeign(['customer_address_id']);
            $table->dropColumn([
                'booking_reference',
                'branch_id',
                'appointment_type',
                'service_zone_id',
                'customer_address_id',
                'end_time',
                'total_duration_minutes',
                'total_amount',
                'travel_fee',
                'tip_amount',
                'discount_amount',
                'grand_total',
                'cancellation_reason',
            ]);
        });
    }
};
