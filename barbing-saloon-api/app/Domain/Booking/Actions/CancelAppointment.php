<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;

class CancelAppointment
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::cancelled->value]);

        return $appointment->refresh()->load(['service', 'barber.user', 'customer']);
    }
}
