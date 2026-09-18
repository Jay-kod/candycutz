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
        $appointments = Appointment::query()
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
            'recent_appointments' => $appointments,
            'total_spent' => $totalSpent,
            'total_bookings' => Appointment::where('customer_id', $user->id)->count(),
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
