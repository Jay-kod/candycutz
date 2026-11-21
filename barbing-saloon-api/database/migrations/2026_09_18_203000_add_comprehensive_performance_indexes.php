<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds comprehensive high-cardinality and composite indexes across all high-traffic tables.
     */
    public function up(): void
    {
        // 1. Working Hours (Slot & Availability lookups)
        Schema::table('working_hours', function (Blueprint $table) {
            $table->index(['barber_id', 'day_of_week'], 'working_hours_barber_day_idx');
        });

        // 2. Users (Authentication, Staff, and Customer Role filtering)
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status'], 'users_role_status_idx');
            $table->index('phone', 'users_phone_idx');
        });

        // 3. Barbers (Availability & Chair Management)
        Schema::table('barbers', function (Blueprint $table) {
            $table->index(['is_available', 'display_order'], 'barbers_available_order_idx');
            $table->index('chair_status', 'barbers_chair_status_idx');
        });

        // 4. Services (Catalog queries by category, status, and featured state)
        Schema::table('services', function (Blueprint $table) {
            $table->index(['is_active', 'category_id', 'display_order'], 'services_active_cat_order_idx');
            $table->index(['is_active', 'is_featured'], 'services_active_featured_idx');
        });

        // 5. Appointments (Calendar, Daily Schedules, Contact Lookups, and Reports)
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['appointment_date', 'status'], 'appointments_date_status_idx');
            $table->index(['client_phone', 'appointment_date'], 'appointments_phone_date_idx');
            $table->index(['client_email', 'appointment_date'], 'appointments_email_date_idx');
            $table->index('created_at', 'appointments_created_at_idx');
        });

        // 6. Payments (Verification Queue, SLA Tracking, and History)
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'payments_status_created_idx');
            $table->index(['status', 'sla_expires_at'], 'payments_status_sla_idx');
            $table->index(['customer_id', 'created_at'], 'payments_customer_created_idx');
            $table->index('verified_by_user_id', 'payments_verified_by_idx');
        });

        // 7. Notifications (Fast Unread Badge & Chronological Delivery)
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['recipient_id', 'is_read', 'created_at'], 'notifications_recip_read_created_idx');
        });

        // 8. Testimonials (Approved Showcase & Barber Reviews)
        Schema::table('testimonials', function (Blueprint $table) {
            $table->index(['is_approved', 'is_featured'], 'testimonials_approved_featured_idx');
            $table->index(['barber_id', 'is_approved'], 'testimonials_barber_approved_idx');
        });

        // 9. Gallery (Category browsing & Featured showcase)
        Schema::table('gallery', function (Blueprint $table) {
            $table->index(['is_featured', 'display_order'], 'gallery_featured_order_idx');
            $table->index(['category', 'display_order'], 'gallery_category_order_idx');
        });

        // 10. Blog Posts (Published posts chronologically)
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'blog_posts_status_published_idx');
        });

        // 11. Audit Logs (Module and User chronologies)
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['module', 'created_at'], 'audit_logs_module_created_idx');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_user_created_idx');
            $table->dropIndex('audit_logs_module_created_idx');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex('blog_posts_status_published_idx');
        });

        Schema::table('gallery', function (Blueprint $table) {
            $table->dropIndex('gallery_category_order_idx');
            $table->dropIndex('gallery_featured_order_idx');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropIndex('testimonials_barber_approved_idx');
            $table->dropIndex('testimonials_approved_featured_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_recip_read_created_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_verified_by_idx');
            $table->dropIndex('payments_customer_created_idx');
            $table->dropIndex('payments_status_sla_idx');
            $table->dropIndex('payments_status_created_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_created_at_idx');
            $table->dropIndex('appointments_email_date_idx');
            $table->dropIndex('appointments_phone_date_idx');
            $table->dropIndex('appointments_date_status_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_active_featured_idx');
            $table->dropIndex('services_active_cat_order_idx');
        });

        Schema::table('barbers', function (Blueprint $table) {
            $table->dropIndex('barbers_chair_status_idx');
            $table->dropIndex('barbers_available_order_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_phone_idx');
            $table->dropIndex('users_role_status_idx');
        });

        Schema::table('working_hours', function (Blueprint $table) {
            $table->dropIndex('working_hours_barber_day_idx');
        });
    }
};
