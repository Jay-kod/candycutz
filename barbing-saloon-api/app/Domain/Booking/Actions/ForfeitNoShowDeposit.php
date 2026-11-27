<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\NoShowPolicyService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\Appointment;
use App\Models\NoShowRecord;
use App\Models\User;
use App\Support\Errors\BookingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ForfeitNoShowDeposit
{
    public function __construct(
        protected NoShowPolicyService $policyService
    ) {}

    /**
     * Explicitly mark a no-show appointment's paid deposit as forfeited.
     */
    public function execute(Appointment $appointment, User $actor, ?string $reason = null): NoShowRecord
    {
        /** @var array{record: NoShowRecord, appointment: Appointment, amount_naira: float} $result */
        $result = DB::transaction(function () use ($appointment, $actor, $reason): array {
            /** @var Appointment $locked */
            $locked = Appointment::with(['payment', 'noShowRecord', 'customer'])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $statusVal = $locked->status instanceof AppointmentStatus
                ? $locked->status->value
                : (string) $locked->status;

            if ($statusVal !== AppointmentStatus::no_show->value) {
                throw BookingException::invalidTransition('Deposit can only be forfeited for an appointment marked as no-show.');
            }

            $hasPaidPayment = (bool) ($locked->deposit_paid || ($locked->payment && $locked->payment->status?->isSuccess()));
            if (! $hasPaidPayment) {
                throw ValidationException::withMessages([
                    'appointment_id' => ['This appointment has no paid deposit to forfeit.'],
                ]);
            }

            if ($locked->deposit_forfeited) {
                throw ValidationException::withMessages([
                    'appointment_id' => ['The deposit for this appointment has already been marked as forfeited.'],
                ]);
            }

            $now = now();
            $amountMinor = $this->policyService->resolveDepositMinor($locked);

            $locked->update([
                'deposit_forfeited' => true,
                'deposit_forfeited_at' => $now,
                'deposit_forfeited_by' => $actor->id,
            ]);

            $record = $locked->noShowRecord;
            if (! $record) {
                $record = NoShowRecord::create([
                    'appointment_id' => $locked->id,
                    'customer_id' => $locked->customer_id,
                    'marked_at' => $locked->no_show_marked_at ?? $now,
                    'marked_by' => $locked->no_show_marked_by,
                    'reason' => $reason ?: 'Marked no-show',
                    'was_automatic' => (bool) $locked->no_show_auto,
                    'deposit_forfeited' => true,
                    'deposit_forfeited_minor' => $amountMinor,
                    'deposit_forfeited_at' => $now,
                    'deposit_forfeited_by' => $actor->id,
                ]);
            } else {
                $record->update([
                    'deposit_forfeited' => true,
                    'deposit_forfeited_minor' => $amountMinor,
                    'deposit_forfeited_at' => $now,
                    'deposit_forfeited_by' => $actor->id,
                ]);
            }

            return [
                'record' => $record->fresh(['appointment.service', 'appointment.barber.user', 'appointment.payment', 'customer', 'forfeitedBy']),
                'appointment' => $locked->fresh(['service', 'barber.user', 'customer', 'payment', 'noShowRecord']),
                'amount_naira' => $amountMinor / 100,
            ];
        }, 3);

        try {
            NotificationDispatcher::noShowDepositForfeited($result['appointment'], $result['amount_naira'], $reason);
        } catch (\Throwable $e) {
            Log::warning("ForfeitNoShowDeposit notification failed: {$e->getMessage()}");
        }

        try {
            AuditLogger::log(
                'no_show.deposit_forfeited',
                'Payment',
                $actor,
                ['deposit_forfeited' => false],
                ['deposit_forfeited' => true, 'amount_minor' => $result['record']->deposit_forfeited_minor, 'reason' => $reason],
                Appointment::class,
                $result['appointment']->id
            );
        } catch (\Throwable) {
            // Non-fatal audit logging failure
        }

        return $result['record'];
    }
}
