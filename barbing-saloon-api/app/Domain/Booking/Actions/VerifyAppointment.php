<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyAppointment
{
    public function execute(int $id): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => AppointmentStatus::completed->value,
            'deposit_paid' => true,
        ]);

        if (Schema::hasTable('payments')) {
            DB::table('payments')
                ->where('appointment_id', $appointment->id)
                ->update(['status' => 'successful', 'updated_at' => now()]);
        }

        return $appointment;
    }
}
