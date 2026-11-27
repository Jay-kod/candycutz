<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    private const CUSTOMER_ACCOUNT_ACTIVITY_ACTIONS = [
        'auth.login',
        'auth.social_login',
        'auth.reset_password',
        'auth.change_password',
        'profile.updated',
        'session_revoked',
        'sessions_revoked_others',
    ];

    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        $today = today()->toDateString();

        $totalBookings = Appointment::where('customer_id', $user->id)->count();

        $upcomingBookings = Appointment::where('customer_id', $user->id)
            ->where('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->count();

        $completedBookings = Appointment::where('customer_id', $user->id)
            ->where('status', AppointmentStatus::completed->value)
            ->count();

        $reviewsCount = Testimonial::where('customer_id', $user->id)->count();

        $upcomingAppointments = Appointment::query()
            ->with(['service.category', 'barber.user', 'serviceZone', 'customer', 'payment'])
            ->where('customer_id', $user->id)
            ->where('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get()
            ->map(function ($a) {
                $res = (new AppointmentResource($a))->resolve();

                return [
                    'id' => $a->id,
                    'booking_reference' => $res['booking_reference'] ?? "CC-{$a->id}",
                    'appointment_date' => $a->appointment_date,
                    'appointment_time' => substr((string) $a->appointment_time, 0, 5),
                    'status' => $a->status->value ?? $a->status,
                    'verification_code' => $a->verification_code,
                    'total_price' => (float) ($a->total_price ?? $a->grand_total ?? $a->service->price ?? 0),
                    'grand_total' => (float) ($a->grand_total ?? $a->total_price ?? $a->service->price ?? 0),
                    'payment_status' => $res['payment_status'] ?? 'pending',
                    'deposit_paid' => (bool) $a->deposit_paid,
                    'bank_details' => $res['bank_details'] ?? null,
                    'notes' => $a->notes,
                    'service' => $a->service ? [
                        'id' => $a->service->id,
                        'name' => $a->service->name,
                        'price' => (float) $a->service->price,
                    ] : null,
                    'barber' => $a->barber ? [
                        'id' => $a->barber->id,
                        'name' => $a->barber->user->name ?? 'Barber',
                    ] : null,
                ];
            });

        $recentAppointments = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(5)
            ->get();

        $totalSpent = (float) Appointment::where('customer_id', $user->id)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        return [
            'stats' => [
                'total_bookings' => $totalBookings,
                'upcoming_bookings' => $upcomingBookings,
                'completed_bookings' => $completedBookings,
                'reviews_count' => $reviewsCount,
            ],
            'upcoming_appointments' => $upcomingAppointments,
            'recent_appointments' => $recentAppointments,
            'account_activities' => $this->recentAccountActivities($user),
            'total_spent' => $totalSpent,
            'total_bookings' => $totalBookings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(User $user): array
    {
        $appointments = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get();

        return [
            'appointments' => $appointments,
            'account_activities' => $this->recentAccountActivities($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analytics(User $user, string $range): array
    {
        $today = today();
        $periodStart = match ($range) {
            '7d' => today()->subDays(6),
            '30d' => today()->subDays(29),
            'month' => today()->startOfMonth(),
            default => null,
        };

        $appointmentsQuery = Appointment::query()
            ->with(['service:id,name'])
            ->where('customer_id', $user->id)
            ->where('status', '!=', AppointmentStatus::draft->value);

        if ($periodStart !== null) {
            $appointmentsQuery
                ->whereDate('appointment_date', '>=', $periodStart->toDateString())
                ->whereDate('appointment_date', '<=', $today->toDateString());
        }

        $appointments = $appointmentsQuery
            ->get(['id', 'service_id', 'status', 'total_price', 'appointment_date']);

        $statusBreakdown = collect(AppointmentStatus::cases())
            ->reject(fn (AppointmentStatus $status): bool => $status === AppointmentStatus::draft)
            ->mapWithKeys(fn (AppointmentStatus $status): array => [$status->value => 0])
            ->all();
        $periodCounts = [];
        $serviceCounts = [];

        foreach ($appointments as $appointment) {
            $status = $appointment->status instanceof AppointmentStatus
                ? $appointment->status->value
                : (string) $appointment->status;
            if (array_key_exists($status, $statusBreakdown)) {
                $statusBreakdown[$status]++;
            }

            if ($appointment->appointment_date !== null) {
                $date = Carbon::parse($appointment->appointment_date);
                $periodKey = $range === 'all' ? $date->format('Y-m') : $date->toDateString();
                $periodCounts[$periodKey] = ($periodCounts[$periodKey] ?? 0) + 1;
            }

            $serviceName = $appointment->service?->name;
            if ($serviceName !== null) {
                $serviceCounts[$serviceName] = ($serviceCounts[$serviceName] ?? 0) + 1;
            }
        }

        ksort($periodCounts);
        arsort($serviceCounts);

        if ($range === 'all') {
            $bookingTrends = collect($periodCounts)
                ->map(fn (int $count, string $month): array => [
                    'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'count' => $count,
                ])
                ->values()
                ->all();
        } else {
            $bookingTrends = [];
            $date = $periodStart->copy();
            while ($date->lte($today)) {
                $key = $date->toDateString();
                $bookingTrends[] = [
                    'month' => $date->format('M j'),
                    'count' => $periodCounts[$key] ?? 0,
                ];
                $date->addDay();
            }
        }

        $completedAppointments = $appointments->filter(
            fn (Appointment $appointment): bool => $appointment->status === AppointmentStatus::completed
        );
        $spentMinor = (int) $completedAppointments->sum(
            fn (Appointment $appointment): int => (int) $appointment->total_price
        );
        $totalSpent = round($spentMinor / 100, 2);

        $allTimeCompletedSpend = Appointment::query()
            ->where('customer_id', $user->id)
            ->where('status', AppointmentStatus::completed->value)
            ->sum('total_price');

        $monthly = Appointment::query()
            ->where('customer_id', $user->id)
            ->whereDate('appointment_date', '>=', today()->subMonths(5)->startOfMonth()->toDateString())
            ->get(['appointment_date', 'total_price'])
            ->groupBy(fn (Appointment $appointment): string => Carbon::parse($appointment->appointment_date)->format('Y-m'))
            ->map(fn ($monthAppointments, string $month): array => [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('F'),
                'bookings' => $monthAppointments->count(),
                'spent' => round((int) $monthAppointments->sum('total_price') / 100, 2),
            ])
            ->values();

        return [
            'booking_trends' => $bookingTrends,
            'status_breakdown' => $statusBreakdown,
            'spending_summary' => [
                'total_spent' => $totalSpent,
                'avg_spent' => $completedAppointments->isNotEmpty()
                    ? round($spentMinor / $completedAppointments->count() / 100, 2)
                    : 0,
                'most_booked' => array_key_first($serviceCounts),
            ],
            'activity_timeline' => [
                'wishlist_items' => DB::table('wishlists')->where('customer_id', $user->id)->count(),
                'reviews_given' => Testimonial::query()->where('customer_id', $user->id)->count(),
            ],
            'total_spent' => round((int) $allTimeCompletedSpend / 100, 2),
            'monthly' => $monthly,
        ];
    }

    /**
     * Return a safe, user-scoped summary of successful account and security events.
     *
     * @return array<int, array{id: int, action: string, title: string, message: string, created_at: ?string}>
     */
    private function recentAccountActivities(User $user): array
    {
        return AuditLog::query()
            ->whereIn('action', self::CUSTOMER_ACCOUNT_ACTIVITY_ACTIONS)
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhere(function (Builder $targetQuery) use ($user): void {
                        $targetQuery
                            ->whereIn('target_type', [User::class, class_basename(User::class)])
                            ->where('target_id', $user->id);
                    });
            })
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'action', 'new_value', 'created_at'])
            ->map(function (AuditLog $auditLog): array {
                $metadata = $auditLog->new_value ?? [];
                [$title, $message] = match ($auditLog->action) {
                    'auth.login' => ['Signed in', 'You signed in to your account.'],
                    'auth.social_login' => [
                        'Signed in with '.match (strtolower((string) ($metadata['provider'] ?? ''))) {
                            'google' => 'Google',
                            'apple' => 'Apple',
                            default => 'a connected account',
                        },
                        'You signed in to your account.',
                    ],
                    'auth.reset_password' => ['Password reset', 'Your account password was reset.'],
                    'auth.change_password' => ['Password changed', 'Your account password was changed.'],
                    'session_revoked' => ['Session signed out', 'A session was removed from your account.'],
                    'sessions_revoked_others' => ['Other sessions signed out', 'Other active sessions were signed out.'],
                    'profile.updated' => ['Account details updated', $this->profileUpdateMessage($metadata)],
                    default => ['Account activity', 'An account security event was recorded.'],
                };

                return [
                    'id' => $auditLog->id,
                    'action' => $auditLog->action,
                    'title' => $title,
                    'message' => $message,
                    'created_at' => $auditLog->created_at?->toISOString(),
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function profileUpdateMessage(array $metadata): string
    {
        $labels = [
            'name' => 'name',
            'username' => 'username',
            'email' => 'email address',
            'phone' => 'phone number',
            'bio' => 'profile details',
            'avatar' => 'profile photo',
        ];
        $changedFields = array_values(array_intersect_key(
            $labels,
            array_flip(is_array($metadata['changed_fields'] ?? null) ? $metadata['changed_fields'] : [])
        ));

        return $changedFields === []
            ? 'Your account details were updated.'
            : 'Updated '.implode(', ', $changedFields).'.';
    }
}
