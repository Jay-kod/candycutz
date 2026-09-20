<?php

namespace App\Domain\System\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\User;
use Carbon\Carbon;

class GetReports
{
    /**
     * @return array<string, mixed>
     */
    public function execute(?string $range = '7d'): array
    {
        $startDate = match ($range) {
            '30d' => today()->subDays(30)->startOfDay(),
            'month' => today()->startOfMonth(),
            'all' => Carbon::createFromTimestamp(0),
            default => today()->subDays(7)->startOfDay(),
        };

        // Business Stats
        $totalRevenue = (float) Appointment::query()
            ->where('appointment_date', '>=', $startDate->toDateString())
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        $totalAppointments = Appointment::query()
            ->where('appointment_date', '>=', $startDate->toDateString())
            ->where('status', AppointmentStatus::completed->value)
            ->count();

        $newCustomers = User::query()
            ->where('role', 'customer')
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalCustomers = User::query()
            ->where('role', 'customer')
            ->count();

        $businessStats = [
            'total_revenue' => $totalRevenue,
            'total_appointments' => $totalAppointments,
            'new_customers' => $newCustomers,
            'total_customers' => $totalCustomers,
        ];

        // Platform Stats
        $platformStats = [
            'active_barbers' => Barber::query()->where('is_available', true)->count(),
            'total_services' => Service::query()->count(),
            'total_reviews' => Testimonial::query()->count(),
            'avg_rating' => (float) (Barber::query()->avg('rating') ?: 5.0),
        ];

        // Top Barbers
        $topBarbers = Barber::query()
            ->with('user')
            ->withCount(['appointments as bookings' => function ($q) use ($startDate) {
                $q->where('appointment_date', '>=', $startDate->toDateString())
                  ->where('status', AppointmentStatus::completed->value);
            }])
            ->get()
            ->map(function ($b) use ($startDate) {
                $rev = (float) Appointment::query()
                    ->where('barber_id', $b->id)
                    ->where('appointment_date', '>=', $startDate->toDateString())
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                return [
                    'id' => $b->id,
                    'name' => $b->user?->name ?? 'Master Barber',
                    'bookings' => (int) $b->getAttribute('bookings'),
                    'revenue' => $rev,
                    'rating' => (float) ($b->getAttribute('rating') ?: 5.0),
                ];
            })
            ->sortByDesc('bookings')
            ->values()
            ->all();

        // Top Services
        $topServices = Service::query()
            ->with('category')
            ->withCount(['appointments as count' => function ($q) use ($startDate) {
                $q->where('appointment_date', '>=', $startDate->toDateString());
            }])
            ->get()
            ->map(function ($s) use ($startDate) {
                $rev = (float) Appointment::query()
                    ->where('service_id', $s->id)
                    ->where('appointment_date', '>=', $startDate->toDateString())
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'category' => $s->category?->name ?? 'General',
                    'bookings' => (int) $s->getAttribute('count'),
                    'count' => (int) $s->getAttribute('count'),
                    'revenue' => $rev,
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        // Status breakdown
        $statusBreakdown = [
            'completed' => Appointment::query()->where('appointment_date', '>=', $startDate->toDateString())->where('status', AppointmentStatus::completed->value)->count(),
            'confirmed' => Appointment::query()->where('appointment_date', '>=', $startDate->toDateString())->where('status', AppointmentStatus::confirmed->value)->count(),
            'pending' => Appointment::query()->where('appointment_date', '>=', $startDate->toDateString())->where('status', AppointmentStatus::pending->value)->count(),
            'cancelled' => Appointment::query()->where('appointment_date', '>=', $startDate->toDateString())->where('status', AppointmentStatus::cancelled->value)->count(),
            'no_show' => Appointment::query()->where('appointment_date', '>=', $startDate->toDateString())->where('status', AppointmentStatus::no_show->value)->count(),
        ];

        // Revenue trends & Booking trends
        $revenueTrends = [];
        $bookingTrends = [];
        $daysCount = match ($range) {
            '30d' => 30,
            'month' => 30,
            'all' => 6,
            default => 7,
        };

        if ($range === 'all') {
            for ($m = 5; $m >= 0; $m--) {
                $monthDate = today()->subMonths($m);
                $monthLabel = $monthDate->format('M');
                $mRev = (float) Appointment::query()
                    ->whereYear('appointment_date', $monthDate->year)
                    ->whereMonth('appointment_date', $monthDate->month)
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');
                $mCount = Appointment::query()
                    ->whereYear('appointment_date', $monthDate->year)
                    ->whereMonth('appointment_date', $monthDate->month)
                    ->count();

                $revenueTrends[] = ['month' => $monthLabel, 'revenue' => $mRev];
                $bookingTrends[] = ['month' => $monthLabel, 'count' => $mCount];
            }
        } else {
            for ($d = $daysCount - 1; $d >= 0; $d--) {
                $dayDate = today()->subDays($d);
                $dayLabel = $dayDate->format('D');
                $dRev = (float) Appointment::query()
                    ->whereDate('appointment_date', $dayDate->toDateString())
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');
                $dCount = Appointment::query()
                    ->whereDate('appointment_date', $dayDate->toDateString())
                    ->count();

                $revenueTrends[] = ['month' => $dayLabel, 'revenue' => $dRev];
                $bookingTrends[] = ['month' => $dayLabel, 'count' => $dCount];
            }
        }

        return [
            'business_stats' => $businessStats,
            'platform_stats' => $platformStats,
            'top_barbers' => $topBarbers,
            'top_services' => $topServices,
            'status_breakdown' => $statusBreakdown,
            'revenue_trends' => $revenueTrends,
            'booking_trends' => $bookingTrends,

            // Legacy keys preserved
            'appointments_by_status' => Appointment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'services_by_category' => ServiceCategory::query()->withCount('services')->orderBy('display_order')->get(),
            'featured_barbers' => Barber::query()->where('is_featured', true)->with('user')->orderBy('display_order')->get(),
            'revenue_estimate' => Appointment::query()->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])->sum('total_price'),
        ];
    }
}
