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

class GetDashboardStats
{
    public function __construct(protected GetReports $getReports) {}

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
                    'revenue' => $revenue,
                ];
            })
            ->all();

        // Recent appointments
        $recentAppointments = Appointment::query()
            ->with(['service', 'customer', 'barber.user'])
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(6)
            ->get();

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
        $barberPerformance = Barber::query()
            ->with('user')
            ->withCount(['appointments as total_appointments'])
            ->get()
            ->map(function ($b) {
                $completed = Appointment::query()
                    ->where('barber_id', $b->id)
                    ->where('status', AppointmentStatus::completed->value)
                    ->count();

                return [
                    'id' => $b->id,
                    'name' => $b->user?->name ?? 'Barber '.$b->id,
                    'total_appointments' => $b->total_appointments,
                    'completed_appointments' => $completed,
                    'rating' => (float) ($b->rating ?? 5.0),
                ];
            })
            ->all();

        return [
            'stats' => [
                'appointments_today' => Appointment::query()->whereDate('appointment_date', $today)->count(),
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
                'month_revenue' => $monthRevenue,
            ],
            'revenue_trend' => $revenueTrend,
            'booking_trend' => $bookingTrend,
            'status_breakdown' => $statusBreakdown,
            'top_services' => $topServices,
            'recent_appointments' => $recentAppointments,
            'recent_activity' => $recentActivity,
            'barber_performance' => $barberPerformance,
            'reports' => $this->getReports->execute(),
        ];
    }
}
