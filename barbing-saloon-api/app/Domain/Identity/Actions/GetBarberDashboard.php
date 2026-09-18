<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;

class GetBarberDashboard
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Barber $barber): array
    {
        $today = today()->toDateString();
        $todayAppointments = Appointment::with(['service', 'customer'])
            ->where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        $upcomingCount = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->count();

        $completedToday = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->where('status', AppointmentStatus::completed->value)
            ->count();

        return [
            'today_appointments' => $todayAppointments,
            'stats' => [
                'upcoming' => $upcomingCount,
                'completed_today' => $completedToday,
            ],
        ];
    }
}
