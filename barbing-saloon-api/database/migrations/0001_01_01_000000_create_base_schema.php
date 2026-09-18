<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('CandyCutz');
            $table->string('legal_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->default(8.84860000);
            $table->decimal('longitude', 11, 8)->default(7.87360000);
            $table->string('phone', 30);
            $table->string('email');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 30)->unique()->nullable();
            $table->string('real_name')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'barber', 'customer']);
            $table->string('auth_provider', 50)->default('local');
            $table->string('provider_id', 150)->nullable();
            $table->enum('status', ['active', 'username_pending', 'deactivated', 'suspended'])->default('active');
            $table->timestamp('last_username_change_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100)->default('Home');
            $table->text('street');
            $table->string('landmark')->nullable();
            $table->string('city', 100)->default('Keffi');
            $table->string('state', 100)->default('Nasarawa');
            $table->string('postal_code', 20)->default('961101');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->text('bio')->nullable();
            $table->json('specialties')->nullable();
            $table->unsignedInteger('years_experience')->default(0);
            $table->string('instagram_url')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_home_service_ready')->default(true);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_reviews')->default(0);
            $table->string('chair_status', 20)->default('free');
            $table->unsignedInteger('experience_years')->nullable();
            $table->timestamps();
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('duration_minutes');
            $table->boolean('home_service_allowed')->default(true);
            $table->foreignId('category_id')->constrained('service_categories');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('service_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('boundary_polygon')->nullable();
            $table->decimal('radius_km', 6, 2)->default(15.00);
            $table->decimal('base_travel_fee', 10, 2)->default(2000.00);
            $table->decimal('per_km_fee', 10, 2)->default(150.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('barber_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('barbers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->integer('custom_duration')->nullable();
            $table->boolean('is_offered')->default(true);
            $table->timestamps();

            $table->unique(['barber_id', 'service_id']);
        });

        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->nullable()->constrained('barbers');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('blocked_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('barbers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['barber_id', 'start_datetime', 'end_datetime'], 'blocked_periods_b_id_start_end_idx');
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 30)->unique()->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users');
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email');
            $table->foreignId('barber_id')->constrained('barbers');
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->enum('appointment_type', ['in_shop', 'home_service'])->default('in_shop');
            $table->foreignId('service_zone_id')->nullable()->constrained('service_zones')->nullOnDelete();
            $table->foreignId('customer_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->time('end_time')->nullable();
            $table->integer('total_duration_minutes')->default(30);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('travel_fee', 10, 2)->default(0.00);
            $table->decimal('tip_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->decimal('total_price', 8, 2);
            $table->boolean('deposit_paid')->default(false);
            $table->decimal('deposit_amount', 8, 2)->default(0.00);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['appointment_date', 'barber_id']);
            $table->index('status');
            $table->index(['barber_id', 'appointment_date', 'status'], 'appointments_barber_date_status_idx');
            $table->index(['customer_id', 'status'], 'appointments_customer_status_idx');
        });

        Schema::create('appointment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(30);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('appointment_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['appointment_id', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('NGN');
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method', 50)->default('stripe');
            $table->string('stripe_payment_intent_id', 150)->nullable()->index();
            $table->string('stripe_charge_id', 150)->nullable();
            $table->string('transaction_ref', 100)->unique();
            $table->string('receipt_url')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'status']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->enum('transaction_type', ['authorization', 'capture', 'refund', 'void']);
            $table->string('gateway', 50)->default('stripe');
            $table->string('gateway_event_id', 150)->nullable()->unique();
            $table->decimal('amount', 10, 2);
            $table->json('raw_payload')->nullable();
            $table->string('status', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['payment_id', 'transaction_type']);
        });

        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->enum('category', ['haircut', 'beard', 'combo', 'before_after', 'shop']);
            $table->foreignId('barber_id')->nullable()->constrained('barbers');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users');
            $table->string('client_name');
            $table->string('client_avatar')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->foreignId('service_id')->nullable()->constrained('services');
            $table->foreignId('barber_id')->nullable()->constrained('barbers');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('body');
            $table->string('featured_image')->nullable();
            $table->foreignId('author_id')->constrained('users');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('theme_name', 100)->default('CandyCutz Royal');
            $table->json('tokens_json');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });

        Schema::create('theme_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_setting_id')->constrained('theme_settings')->cascadeOnDelete();
            $table->integer('version_number');
            $table->json('tokens_json');
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['theme_setting_id', 'version_number']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('user_role')->nullable();
            $table->string('action');
            $table->string('module');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->enum('platform', ['ios', 'android', 'web'])->default('android');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_type', 64)->default('customer');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('type', 64)->default('system');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('recipient_id');
            $table->index('created_at');
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Add the partial unique index via raw SQL for DBs that support it (SQLite does)
        if (config('database.default') === 'sqlite') {
            DB::statement(
                "CREATE UNIQUE INDEX appointments_barber_date_time_unique ON appointments(barber_id, appointment_date, appointment_time) WHERE status NOT IN ('cancelled', 'no_show')"
            );
        } else {
            // MySQL 8+ supports functional indexes, but Laravel blueprint doesn't do partial cleanly.
            // As a fallback for MySQL, we can just leave it to application lock, or try functional index in future.
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('theme_versions');
        Schema::dropIfExists('theme_settings');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('gallery');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('appointment_status_history');
        Schema::dropIfExists('appointment_items');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('blocked_periods');
        Schema::dropIfExists('working_hours');
        Schema::dropIfExists('barber_services');
        Schema::dropIfExists('service_zones');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('barbers');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('businesses');
    }
};
