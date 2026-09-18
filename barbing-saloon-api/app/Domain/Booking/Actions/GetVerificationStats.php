<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;

class GetVerificationStats
{
    /**
     * @return array<string, int>
     */
    public function execute(): array
    {
        $total = Appointment::query()->count();
        $verifiedToday = Appointment::query()
            ->where('status', AppointmentStatus::completed->value)
            ->whereDate('updated_at', today())
            ->count();
        $pending = Appointment::query()
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->whereDate('appointment_date', '>=', today())
            ->count();
        $expired = Appointment::query()
            ->where('appointment_date', '<', today())
            ->where('status', '!=', AppointmentStatus::completed->value)
            ->count();

        return [
            'total' => $total,
            'verified_today' => $verifiedToday,
            'pending' => $pending,
            'expired' => $expired,
        ];
    }
}
