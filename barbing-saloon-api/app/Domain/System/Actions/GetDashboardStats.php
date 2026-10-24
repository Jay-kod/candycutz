<?php

declare(strict_types=1);

namespace App\Domain\System\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\BlogStatus;
use App\Domain\System\Services\SalonCalculationEngine;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class GetDashboardStats
{
    public function __construct(
        protected GetReports $getReports,
        protected SalonCalculationEngine $engine
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $today = today();

        // 1. Authoritative metrics computed via the SalonCalculationEngine
        $summary = $this->engine->computeSummaryMetrics($today);
        $revenue = $summary['revenue'];
        $appts = $summary['appointments'];
        $statusBreakdown = $summary['status_breakdown'];

        // 2. High-performance timeline and distribution calculations
        $timelines = $this->engine->computeDailyTimeline($today);
        $monthlyRevenueTrend = $this->engine->computeMonthlyTimeline($today);
        $hourlyDistribution = $this->engine->computeHourlyDistribution();

        // 3. Entity performance
        $topServices = $this->engine->computeTopServices(5);
        $topBarbers = $this->engine->computeTopBarbers(10);

        // 4. Customer counts
        $totalCustomers = User::query()->where('role', 'customer')->count();
        $newCustomersWeek = User::query()->where('role', 'customer')->where('created_at', '>=', now()->subDays(7))->count();

        // 5. Live Database Connection & System Health Status
        $dbStatus = 'disconnected';
        $dbLatencyMs = 0;
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $dbLatencyMs = max((int) round((microtime(true) - $start) * 1000), 1);
            $dbStatus = 'connected';
        } catch (Throwable) {
            $dbStatus = 'disconnected';
        }

        $systemHealth = [
            'status' => $dbStatus === 'connected' ? 'operational' : 'degraded',
            'database' => [
                'status' => $dbStatus,
                'connection' => config('database.default', 'mysql'),
                'latency_ms' => $dbLatencyMs,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        // 6. Recent appointments with eager-loaded service, barber, and customer
        $recentAppointments = Appointment::query()
            ->with(['service', 'customer', 'barber.user'])
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(6)
            ->get()
            ->map(function ($appt) {
                return [
                    'id' => $appt->id,
                    'client_name' => $appt->client_name ?: ($appt->customer?->name ?? 'Walk-In Client'),
                    'appointment_date' => $appt->appointment_date?->toDateString(),
                    'appointment_time' => substr((string) $appt->appointment_time, 0, 5),
                    'status' => $appt->status?->value ?? $appt->status,
                    'price' => (float) ($appt->service?->price ?? $appt->total_price ?? 0),
                    'total_price' => (float) ($appt->total_price ?? $appt->service?->price ?? 0),
                    'service' => $appt->service ? [
                        'id' => $appt->service->id,
                        'name' => $appt->service->name,
                        'price' => (float) $appt->service->price,
                    ] : null,
                    'barber' => $appt->barber ? [
                        'id' => $appt->barber->id,
                        'name' => $appt->barber->user?->name ?? 'Master Barber',
                    ] : null,
                    'customer' => $appt->customer ? [
                        'id' => $appt->customer->id,
                        'name' => $appt->customer->name,
                    ] : null,
                ];
            })
            ->all();

        // 7. Recent activity from AuditLog
        $recentActivity = AuditLog::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'description' => $log->description ?? $log->action,
                    'created_at' => $log->created_at?->toIso8601String() ?? now()->toIso8601String(),
                ];
            })
            ->all();

        return [
            'stats' => [
                'appointments_today' => $appts['today'],
                'today_appointments' => $appts['today'],
                'today_revenue' => $revenue['today_revenue'],
                'week_revenue' => $revenue['week_revenue'],
                'month_revenue' => $revenue['month_revenue'],
                'appointments_yesterday' => $appts['yesterday'],
                'pending_appointments' => $appts['pending'],
                'total_customers' => $totalCustomers,
                'new_customers_week' => $newCustomersWeek,
                'completion_rate' => $appts['completion_rate'],
                'average_ticket' => $appts['average_ticket'],
                'total_appointments' => $appts['total'],
                'published_blog_posts' => BlogPost::query()->where('status', BlogStatus::published->value)->count(),
                'gallery_items' => Gallery::query()->count(),
                'services' => Service::query()->count(),
                'barbers' => Barber::query()->count(),
                'testimonials' => Testimonial::query()->count(),
            ],
            'revenue' => [
                'total_revenue' => $revenue['total_revenue'],
                'realized_revenue' => $revenue['realized_revenue'],
                'booked_revenue' => $revenue['booked_revenue'],
                'today_revenue' => $revenue['today_revenue'],
                'week_revenue' => $revenue['week_revenue'],
                'month_revenue' => $revenue['month_revenue'],
            ],
            'revenue_trend' => $timelines['revenue_trend'],
            'booking_trend' => $timelines['booking_trend'],
            'thirty_day_trend' => $timelines['thirty_day_trend'],
            'monthly_revenue_trend' => $monthlyRevenueTrend,
            'hourly_distribution' => $hourlyDistribution,
            'system_health' => $systemHealth,
            'status_breakdown' => $statusBreakdown,
            'top_services' => $topServices,
            'top_barbers' => $topBarbers,
            'recent_appointments' => $recentAppointments,
            'recent_activity' => $recentActivity,
            'barber_performance' => $topBarbers,
            'reports' => $this->getReports->execute(),
        ];
    }
}
