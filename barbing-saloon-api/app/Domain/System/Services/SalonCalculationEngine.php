<?php

declare(strict_types=1);

namespace App\Domain\System\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalonCalculationEngine
{
    /**
     * Canonical SQL expression for calculating an appointment's monetary amount.
     * Reconciles appointments against canonical service price:
     * - Uses appointment total_price if within realistic bounds (1 - 100,000 NGN)
     * - Protects against legacy 10,000x multiplier by dividing if >= 100,000
     * - Seamlessly falls back to canonical service price (services.price) if null or 0
     */
    public const PRICE_EXPR = "COALESCE(CASE WHEN a.total_price > 0 AND a.total_price <= 100000 THEN a.total_price WHEN a.total_price > 100000 THEN ROUND(a.total_price / 10000) ELSE NULL END, s.price, 0)";

    /**
     * Compute comprehensive salon revenue & appointment KPIs in a single optimized query.
     *
     * @return array<string, mixed>
     */
    public function computeSummaryMetrics(?Carbon $referenceDate = null): array
    {
        $today = $referenceDate ? $referenceDate->copy()->startOfDay() : Carbon::today();
        $todayStr = $today->toDateString();
        $yesterdayStr = $today->copy()->subDay()->toDateString();
        $startOfWeek = $today->copy()->startOfWeek()->toDateString();
        $endOfWeek = $today->copy()->endOfWeek()->toDateString();
        $startOfMonth = $today->copy()->startOfMonth()->toDateString();
        $endOfMonth = $today->copy()->endOfMonth()->toDateString();

        $expr = self::PRICE_EXPR;

        $row = DB::table('appointments as a')
            ->leftJoin('services as s', 'a.service_id', '=', 's.id')
            ->selectRaw("
                SUM(CASE WHEN a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as total_revenue,
                SUM(CASE WHEN a.status = 'completed' THEN {$expr} ELSE 0 END) as realized_revenue,
                SUM(CASE WHEN a.status = 'confirmed' THEN {$expr} ELSE 0 END) as booked_revenue,
                SUM(CASE WHEN a.appointment_date = '{$todayStr}' AND a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as today_revenue,
                SUM(CASE WHEN a.appointment_date BETWEEN '{$startOfWeek}' AND '{$endOfWeek}' AND a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as week_revenue,
                SUM(CASE WHEN a.appointment_date BETWEEN '{$startOfMonth}' AND '{$endOfMonth}' AND a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as month_revenue,
                COUNT(CASE WHEN a.appointment_date = '{$todayStr}' THEN 1 END) as appointments_today,
                COUNT(CASE WHEN a.appointment_date = '{$yesterdayStr}' THEN 1 END) as appointments_yesterday,
                COUNT(*) as total_appointments,
                COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_appointments,
                COUNT(CASE WHEN a.status = 'confirmed' THEN 1 END) as confirmed_appointments,
                COUNT(CASE WHEN a.status = 'pending' THEN 1 END) as pending_appointments,
                COUNT(CASE WHEN a.status = 'cancelled' THEN 1 END) as cancelled_appointments,
                COUNT(CASE WHEN a.status = 'no_show' THEN 1 END) as no_show_appointments
            ")
            ->first();

        $totalRev = (float) ($row->total_revenue ?? 0);
        $realizedRev = (float) ($row->realized_revenue ?? 0);
        $bookedRev = (float) ($row->booked_revenue ?? 0);
        $todayRev = (float) ($row->today_revenue ?? 0);
        $weekRev = (float) ($row->week_revenue ?? 0);
        $monthRev = (float) ($row->month_revenue ?? 0);

        $totalAppts = (int) ($row->total_appointments ?? 0);
        $completedAppts = (int) ($row->completed_appointments ?? 0);
        $confirmedAppts = (int) ($row->confirmed_appointments ?? 0);
        $pendingAppts = (int) ($row->pending_appointments ?? 0);
        $cancelledAppts = (int) ($row->cancelled_appointments ?? 0);
        $noShowAppts = (int) ($row->no_show_appointments ?? 0);

        $completionRate = $totalAppts > 0 ? (int) round(($completedAppts / $totalAppts) * 100) : 0;
        $averageTicket = $completedAppts > 0
            ? (float) round($realizedRev / $completedAppts, 2)
            : ($totalAppts > 0 ? (float) round($totalRev / $totalAppts, 2) : 0.0);

        return [
            'revenue' => [
                'total_revenue' => $totalRev,
                'realized_revenue' => $realizedRev,
                'booked_revenue' => $bookedRev,
                'today_revenue' => $todayRev,
                'week_revenue' => $weekRev,
                'month_revenue' => $monthRev,
            ],
            'appointments' => [
                'today' => (int) ($row->appointments_today ?? 0),
                'yesterday' => (int) ($row->appointments_yesterday ?? 0),
                'total' => $totalAppts,
                'completed' => $completedAppts,
                'confirmed' => $confirmedAppts,
                'pending' => $pendingAppts,
                'cancelled' => $cancelledAppts,
                'no_show' => $noShowAppts,
                'completion_rate' => $completionRate,
                'average_ticket' => $averageTicket,
            ],
            'status_breakdown' => [
                'completed' => $completedAppts,
                'confirmed' => $confirmedAppts,
                'pending' => $pendingAppts,
                'cancelled' => $cancelledAppts,
                'no_show' => $noShowAppts,
            ],
        ];
    }

    /**
     * Compute rolling 30-day timeline and 7-day performance trend in a single grouped query.
     *
     * @return array{thirty_day_trend: array<int, mixed>, revenue_trend: array<int, mixed>, booking_trend: array<int, mixed>}
     */
    public function computeDailyTimeline(?Carbon $referenceDate = null): array
    {
        $today = $referenceDate ? $referenceDate->copy()->startOfDay() : Carbon::today();
        $thirtyDaysAgo = $today->copy()->subDays(29)->toDateString();
        $expr = self::PRICE_EXPR;

        $rawDays = DB::table('appointments as a')
            ->leftJoin('services as s', 'a.service_id', '=', 's.id')
            ->selectRaw("
                DATE(a.appointment_date) as day_date,
                SUM(CASE WHEN a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as total_rev,
                COUNT(*) as total_count
            ")
            ->where('a.appointment_date', '>=', $thirtyDaysAgo)
            ->groupBy('day_date')
            ->get()
            ->keyBy('day_date');

        $thirtyDayTrend = [];
        $revenueTrend7d = [];
        $bookingTrend7d = [];

        for ($i = 29; $i >= 0; $i--) {
            $d = $today->copy()->subDays($i);
            $dStr = $d->toDateString();
            $dayStr = $d->format('D');
            $matched = $rawDays->get($dStr);

            $rev = (float) ($matched ? $matched->total_rev : 0);
            $cnt = (int) ($matched ? $matched->total_count : 0);

            $entry = [
                'date' => $dStr,
                'day' => $dayStr,
                'date_label' => $d->format('M j'),
                'revenue' => $rev,
                'count' => $cnt,
            ];

            $thirtyDayTrend[] = $entry;

            if ($i < 7) {
                $revenueTrend7d[] = [
                    'day' => $dayStr,
                    'date' => $dStr,
                    'revenue' => $rev,
                ];
                $bookingTrend7d[] = [
                    'day' => $dayStr,
                    'date' => $dStr,
                    'count' => $cnt,
                ];
            }
        }

        return [
            'thirty_day_trend' => $thirtyDayTrend,
            'revenue_trend' => $revenueTrend7d,
            'booking_trend' => $bookingTrend7d,
        ];
    }

    /**
     * Compute rolling 6-month monthly revenue & booking trend in a single grouped query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function computeMonthlyTimeline(?Carbon $referenceDate = null): array
    {
        $today = $referenceDate ? $referenceDate->copy()->startOfDay() : Carbon::today();
        $sixMonthsAgo = $today->copy()->subMonths(5)->startOfMonth()->toDateString();
        $expr = self::PRICE_EXPR;

        $rawMonths = DB::table('appointments as a')
            ->leftJoin('services as s', 'a.service_id', '=', 's.id')
            ->selectRaw("
                DATE_FORMAT(a.appointment_date, '%Y-%m') as ym,
                SUM(CASE WHEN a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as total_rev,
                COUNT(*) as total_count
            ")
            ->where('a.appointment_date', '>=', $sixMonthsAgo)
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $monthlyTrend = [];
        for ($m = 5; $m >= 0; $m--) {
            $mObj = $today->copy()->subMonths($m);
            $ym = $mObj->format('Y-m');
            $matched = $rawMonths->get($ym);

            $monthlyTrend[] = [
                'ym' => $ym,
                'month' => $mObj->format('M'),
                'revenue' => (float) ($matched ? $matched->total_rev : 0),
                'count' => (int) ($matched ? $matched->total_count : 0),
                'is_current' => $m === 0,
            ];
        }

        return $monthlyTrend;
    }

    /**
     * Compute top performing services ranked by real bookings and explicit revenue.
     *
     * @return array<int, array<string, mixed>>
     */
    public function computeTopServices(int $limit = 5, ?string $sinceDate = null): array
    {
        $expr = self::PRICE_EXPR;
        $query = DB::table('services as s')
            ->leftJoin('service_categories as sc', 's.category_id', '=', 'sc.id')
            ->leftJoin('appointments as a', function ($join) use ($sinceDate) {
                $join->on('s.id', '=', 'a.service_id');
                if ($sinceDate !== null) {
                    $join->where('a.appointment_date', '>=', $sinceDate);
                }
            })
            ->selectRaw("
                s.id,
                s.name,
                COALESCE(sc.name, 'General') as category,
                COUNT(a.id) as bookings,
                SUM(CASE WHEN a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as revenue
            ")
            ->groupBy('s.id', 's.name', 'sc.name')
            ->orderByDesc('bookings')
            ->orderByDesc('revenue')
            ->limit($limit);

        return $query->get()->map(function ($s) {
            return [
                'id' => (int) $s->id,
                'name' => (string) $s->name,
                'category' => (string) $s->category,
                'bookings' => (int) $s->bookings,
                'count' => (int) $s->bookings,
                'revenue' => (float) ($s->revenue ?? 0),
            ];
        })->all();
    }

    /**
     * Compute top performing barbers ranked by real bookings and revenue earned from services carried out.
     *
     * @return array<int, array<string, mixed>>
     */
    public function computeTopBarbers(int $limit = 10, ?string $sinceDate = null): array
    {
        $expr = self::PRICE_EXPR;
        $query = DB::table('barbers as b')
            ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
            ->leftJoin('appointments as a', function ($join) use ($sinceDate) {
                $join->on('b.id', '=', 'a.barber_id');
                if ($sinceDate !== null) {
                    $join->where('a.appointment_date', '>=', $sinceDate);
                }
            })
            ->leftJoin('services as s', 'a.service_id', '=', 's.id')
            ->selectRaw("
                b.id,
                COALESCE(u.name, 'Master Barber') as name,
                COALESCE(b.rating, 5.0) as rating,
                COUNT(a.id) as bookings,
                COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_appointments,
                SUM(CASE WHEN a.status IN ('completed', 'confirmed') THEN {$expr} ELSE 0 END) as revenue
            ")
            ->groupBy('b.id', 'u.name', 'b.rating')
            ->orderByDesc('bookings')
            ->orderByDesc('revenue')
            ->limit($limit);

        return $query->get()->map(function ($b) {
            return [
                'id' => (int) $b->id,
                'name' => (string) $b->name,
                'bookings' => (int) $b->bookings,
                'completed_appointments' => (int) $b->completed_appointments,
                'rating' => (float) $b->rating,
                'revenue' => (float) ($b->revenue ?? 0),
            ];
        })->all();
    }

    /**
     * Hourly booking distribution matrix.
     *
     * @return array<int, int>
     */
    public function computeHourlyDistribution(): array
    {
        return DB::table('appointments')
            ->selectRaw("HOUR(appointment_time) as hr, COUNT(*) as count")
            ->whereNotNull('appointment_time')
            ->groupBy('hr')
            ->pluck('count', 'hr')
            ->all();
    }
}
