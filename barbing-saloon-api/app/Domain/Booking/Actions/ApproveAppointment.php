<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;

class ApproveAppointment
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::confirmed->value]);

        return $appointment->refresh()->load(['service', 'barber.user', 'customer']);
    }
}
