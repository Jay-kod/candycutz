<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\AppointmentStateMachine;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyAppointment
{
    public function execute(int $id, ?User $actor = null): Appointment
    {
        $appointment = Appointment::findOrFail($id);

        $previousStatus = $appointment->status instanceof AppointmentStatus
            ? $appointment->status->value
            : (string) $appointment->status;

        $appointment->update([
            'deposit_paid' => true,
            'payment_status' => AppointmentPaymentStatus::paid,
        ]);

        if (Schema::hasTable('payments')) {
            DB::table('payments')
                ->where('appointment_id', $appointment->id)
                ->update(['status' => 'successful', 'updated_at' => now()]);
        }

        if ($previousStatus !== AppointmentStatus::completed->value) {
            if ($actor !== null && AppointmentStateMachine::isAllowed($previousStatus, AppointmentStatus::completed->value)) {
                return app(BookingService::class)->transitionStatus(
                    $appointment,
                    AppointmentStatus::completed->value,
                    $actor,
                    'Session verified and completed by admin'
                );
            }

            $appointment->update([
                'status' => AppointmentStatus::completed->value,
            ]);

            try {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'previous_status' => $previousStatus,
                    'new_status' => AppointmentStatus::completed->value,
                    'changed_by_user_id' => $actor?->id,
                    'reason' => 'Session verified and completed by admin',
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {
                // Keep resilient if audit insert fails
            }

            AppointmentStatusChanged::dispatch(
                $appointment->fresh(),
                $previousStatus,
                AppointmentStatus::completed->value,
                $actor,
                'Session verified and completed by admin'
            );
        }

        return $appointment->fresh();
    }
}
