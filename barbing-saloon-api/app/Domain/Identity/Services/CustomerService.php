<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;

class CustomerService
{
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

        $reviewsCount = \App\Models\Testimonial::where('customer_id', $user->id)->count();

        $upcomingAppointments = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->where('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date,
                'appointment_time' => substr((string) $a->appointment_time, 0, 5),
                'status' => $a->status?->value ?? $a->status,
                'verification_code' => $a->verification_code,
                'total_price' => (float) ($a->total_price ?? $a->grand_total ?? $a->service?->price ?? 0),
                'notes' => $a->notes,
                'service' => $a->service ? [
                    'id' => $a->service->id,
                    'name' => $a->service->name,
                    'price' => (float) $a->service->price,
                ] : null,
                'barber' => $a->barber ? [
                    'id' => $a->barber->id,
                    'name' => $a->barber->user?->name ?? 'Barber',
                ] : null,
            ]);

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
        ];
    }
}
