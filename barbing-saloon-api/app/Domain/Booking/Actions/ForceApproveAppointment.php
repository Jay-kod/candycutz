<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;

class ForceApproveAppointment
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::confirmed->value,
            'deposit_paid' => true,
        ]);

        return $appointment->refresh();
    }
}
