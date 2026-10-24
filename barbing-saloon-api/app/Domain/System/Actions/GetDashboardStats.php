<?php

namespace App\Domain\System\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\BlogStatus;
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
    public function __construct(protected GetReports $getReports) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $today = today();
        $yesterday = today()->subDay();

        $totalAppointments = Appointment::query()->count();
        $completedAppointments = Appointment::query()->where('status', AppointmentStatus::completed->value)->count();
        $completionRate = $totalAppointments > 0 ? (int) round(($completedAppointments / $totalAppointments) * 100) : 0;

        $totalCustomers = User::query()->where('role', 'customer')->count();
        $newCustomersWeek = User::query()->where('role', 'customer')->where('created_at', '>=', now()->subDays(7))->count();

        // Revenue calculations
        $totalRevenue = (float) Appointment::query()
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');
        $todayRevenue = (float) Appointment::query()
            ->whereDate('appointment_date', $today)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');
        $weekRevenue = (float) Appointment::query()
            ->whereBetween('appointment_date', [today()->startOfWeek()->toDateString(), today()->endOfWeek()->toDateString()])
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');
        $monthRevenue = (float) Appointment::query()
            ->whereYear('appointment_date', $today->year)
            ->whereMonth('appointment_date', $today->month)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        // Revenue trend & Booking trend for last 7 days
        $revenueTrend = [];
        $bookingTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = today()->subDays($i);
            $dayStr = $d->format('D');
            $dateStr = $d->toDateString();

            $dayRev = (float) Appointment::query()
                ->whereDate('appointment_date', $dateStr)
                ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                ->sum('total_price');

            $dayCount = Appointment::query()
                ->whereDate('appointment_date', $dateStr)
                ->count();

            $revenueTrend[] = [
                'day' => $dayStr,
                'date' => $dateStr,
                'revenue' => $dayRev,
            ];
            $bookingTrend[] = [
                'day' => $dayStr,
                'date' => $dateStr,
                'count' => $dayCount,
            ];
        }

        // Live Database Connection & System Health Status
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

        // 30-Day Rolling Real Daily Revenue & Bookings Trend
        $thirtyDaysAgo = today()->subDays(29)->toDateString();
        $rawThirtyDayData = Appointment::query()
            ->selectRaw("DATE(appointment_date) as day_date, SUM(CASE WHEN status IN ('confirmed', 'completed') THEN total_price ELSE 0 END) as total_rev, COUNT(*) as total_count")
            ->where('appointment_date', '>=', $thirtyDaysAgo)
            ->groupBy('day_date')
            ->get()
            ->keyBy('day_date');

        $thirtyDayTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateObj = today()->subDays($i);
            $dStr = $dateObj->toDateString();
            $matched = $rawThirtyDayData->get($dStr);

            $thirtyDayTrend[] = [
                'date' => $dStr,
                'day' => $dateObj->format('D'),
                'date_label' => $dateObj->format('M j'),
                'revenue' => (float) ($matched ? $matched->total_rev : 0),
                'count' => (int) ($matched ? $matched->total_count : 0),
            ];
        }

        // 6-Month Rolling Real Monthly Revenue & Bookings Trend
        $sixMonthsAgo = today()->subMonths(5)->startOfMonth()->toDateString();
        $rawMonthlyData = Appointment::query()
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as ym, SUM(CASE WHEN status IN ('confirmed', 'completed') THEN total_price ELSE 0 END) as total_rev, COUNT(*) as total_count")
            ->where('appointment_date', '>=', $sixMonthsAgo)
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $monthlyRevenueTrend = [];
        for ($m = 5; $m >= 0; $m--) {
            $mObj = today()->subMonths($m);
            $ym = $mObj->format('Y-m');
            $matchedMonth = $rawMonthlyData->get($ym);

            $monthlyRevenueTrend[] = [
                'ym' => $ym,
                'month' => $mObj->format('M'),
                'revenue' => (float) ($matchedMonth ? $matchedMonth->total_rev : 0),
                'count' => (int) ($matchedMonth ? $matchedMonth->total_count : 0),
                'is_current' => $m === 0,
            ];
        }

        // Real Hourly Booking Distribution (for Hourly Heatmap Matrix)
        $hourlyDistribution = Appointment::query()
            ->selectRaw("HOUR(appointment_time) as hr, COUNT(*) as count")
            ->whereNotNull('appointment_time')
            ->groupBy('hr')
            ->pluck('count', 'hr')
            ->all();

        // Status breakdown
        $statusBreakdown = [
            'completed' => Appointment::query()->where('status', AppointmentStatus::completed->value)->count(),
            'confirmed' => Appointment::query()->where('status', AppointmentStatus::confirmed->value)->count(),
            'pending' => Appointment::query()->where('status', AppointmentStatus::pending->value)->count(),
            'cancelled' => Appointment::query()->where('status', AppointmentStatus::cancelled->value)->count(),
            'no_show' => Appointment::query()->where('status', AppointmentStatus::no_show->value)->count(),
        ];

        // Top services
        $topServices = Service::query()
            ->with('category')
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                $revenue = (float) Appointment::query()
                    ->where('service_id', $s->id)
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'category' => $s->category?->name ?? 'General',
                    'bookings' => $s->appointments_count,
                    'count' => $s->appointments_count,
                    'revenue' => $revenue,
                ];
            })
            ->all();

        // Top barbers (matches DashboardTopEntities and ReportsTopPerformers)
        $topBarbers = Barber::query()
            ->with('user')
            ->withCount(['appointments as total_appointments'])
            ->get()
            ->map(function ($b) {
                $revenue = (float) Appointment::query()
                    ->where('barber_id', $b->id)
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                $completed = Appointment::query()
                    ->where('barber_id', $b->id)
                    ->where('status', AppointmentStatus::completed->value)
                    ->count();

                return [
                    'id' => $b->id,
                    'name' => $b->user?->name ?? 'Master Barber',
                    'bookings' => (int) $b->getAttribute('total_appointments'),
                    'completed_appointments' => $completed,
                    'rating' => (float) ($b->getAttribute('rating') ?: 5.0),
                    'revenue' => $revenue,
                ];
            })
            ->sortByDesc('bookings')
            ->values()
            ->all();

        // Recent appointments
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
                    'service' => $appt->service ? [
                        'id' => $appt->service->id,
                        'name' => $appt->service->name,
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

        // Recent activity from AuditLog
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

        // Barber performance
        $barberPerformance = $topBarbers;

        return [
            'stats' => [
                'appointments_today' => Appointment::query()->whereDate('appointment_date', $today)->count(),
                'today_appointments' => Appointment::query()->whereDate('appointment_date', $today)->count(),
                'today_revenue' => $todayRevenue,
                'week_revenue' => $weekRevenue,
                'month_revenue' => $monthRevenue,
                'appointments_yesterday' => Appointment::query()->whereDate('appointment_date', $yesterday)->count(),
                'pending_appointments' => Appointment::query()->where('status', AppointmentStatus::pending->value)->count(),
                'total_customers' => $totalCustomers,
                'new_customers_week' => $newCustomersWeek,
                'completion_rate' => $completionRate,
                'total_appointments' => $totalAppointments,
                'published_blog_posts' => BlogPost::query()->where('status', BlogStatus::published->value)->count(),
                'gallery_items' => Gallery::query()->count(),
                'services' => Service::query()->count(),
                'barbers' => Barber::query()->count(),
                'testimonials' => Testimonial::query()->count(),
            ],
            'revenue' => [
                'total_revenue' => $totalRevenue,
                'today_revenue' => $todayRevenue,
                'week_revenue' => $weekRevenue,
                'month_revenue' => $monthRevenue,
            ],
            'revenue_trend' => $revenueTrend,
            'booking_trend' => $bookingTrend,
            'thirty_day_trend' => $thirtyDayTrend,
            'monthly_revenue_trend' => $monthlyRevenueTrend,
            'hourly_distribution' => $hourlyDistribution,
            'system_health' => $systemHealth,
            'status_breakdown' => $statusBreakdown,
            'top_services' => $topServices,
            'top_barbers' => $topBarbers,
            'recent_appointments' => $recentAppointments,
            'recent_activity' => $recentActivity,
            'barber_performance' => $barberPerformance,
            'reports' => $this->getReports->execute(),
        ];
    }
}
