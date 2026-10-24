<?php

declare(strict_types=1);

namespace App\Domain\System\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\System\Services\SalonCalculationEngine;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetReports
{
    public function __construct(
        protected SalonCalculationEngine $engine
    ) {}

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

        $sinceDate = $startDate->toDateString();
        $expr = SalonCalculationEngine::PRICE_EXPR;

        // Business Stats computed explicitly with service price reconciliation
        $statsRow = DB::table('appointments as a')
            ->leftJoin('services as s', 'a.service_id', '=', 's.id')
            ->where('a.appointment_date', '>=', $sinceDate)
            ->selectRaw("
                SUM(CASE WHEN a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as total_rev,
                SUM(CASE WHEN a.status = 'completed' THEN {$expr} ELSE 0 END) as realized_rev,
                COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_count,
                COUNT(*) as total_count
            ")
            ->first();

        $totalRevenue = (float) ($statsRow->total_rev ?? 0);
        $realizedRevenue = (float) ($statsRow->realized_rev ?? 0);
        $totalAppointments = (int) ($statsRow->completed_count ?? 0);

        $newCustomers = User::query()
            ->where('role', 'customer')
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalCustomers = User::query()
            ->where('role', 'customer')
            ->count();

        $businessStats = [
            'total_revenue' => $totalRevenue,
            'realized_revenue' => $realizedRevenue,
            'total_appointments' => $totalAppointments,
            'new_customers' => $newCustomers,
            'total_customers' => $totalCustomers,
            'average_ticket' => $totalAppointments > 0 ? round($realizedRevenue / $totalAppointments, 2) : 0,
        ];

        // Platform Stats
        $platformStats = [
            'active_barbers' => Barber::query()->where('is_available', true)->count(),
            'total_services' => Service::query()->count(),
            'total_reviews' => Testimonial::query()->count(),
            'avg_rating' => (float) (Barber::query()->avg('rating') ?: 5.0),
        ];

        // Top Barbers
        $topBarbers = $this->engine->computeTopBarbers(10, $sinceDate);

        // Top Services
        $topServices = $this->engine->computeTopServices(10, $sinceDate);

        // Status breakdown
        $statusBreakdown = [
            'completed' => Appointment::query()->where('appointment_date', '>=', $sinceDate)->where('status', AppointmentStatus::completed->value)->count(),
            'confirmed' => Appointment::query()->where('appointment_date', '>=', $sinceDate)->where('status', AppointmentStatus::confirmed->value)->count(),
            'pending' => Appointment::query()->where('appointment_date', '>=', $sinceDate)->where('status', AppointmentStatus::pending->value)->count(),
            'cancelled' => Appointment::query()->where('appointment_date', '>=', $sinceDate)->where('status', AppointmentStatus::cancelled->value)->count(),
            'no_show' => Appointment::query()->where('appointment_date', '>=', $sinceDate)->where('status', AppointmentStatus::no_show->value)->count(),
        ];

        // Revenue trends & Booking trends
        $revenueTrends = [];
        $bookingTrends = [];

        if ($range === 'all') {
            $monthlyData = $this->engine->computeMonthlyTimeline(today());
            foreach ($monthlyData as $m) {
                $revenueTrends[] = ['month' => $m['month'], 'revenue' => $m['revenue']];
                $bookingTrends[] = ['month' => $m['month'], 'count' => $m['count']];
            }
        } else {
            $daysCount = match ($range) {
                '30d' => 30,
                'month' => 30,
                default => 7,
            };

            $dailyTimeline = $this->engine->computeDailyTimeline(today());
            $slice = array_slice($dailyTimeline['thirty_day_trend'], -$daysCount);

            foreach ($slice as $entry) {
                $revenueTrends[] = ['month' => $entry['day'], 'date' => $entry['date'], 'revenue' => $entry['revenue']];
                $bookingTrends[] = ['month' => $entry['day'], 'date' => $entry['date'], 'count' => $entry['count']];
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
            'revenue_estimate' => $totalRevenue,
        ];
    }
}
