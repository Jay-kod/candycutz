<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\NoShowPolicyService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RequestGracePeriod
{
    public function __construct(
        protected NoShowPolicyService $policyService
    ) {}

    /**
     * Allow a confirmed customer to extend their check-in deadline once with a valid reason.
     */
    public function execute(Appointment $appointment, User $customer, string $reason): Appointment
    {
        $cleanReason = trim($reason);
        if (mb_strlen($cleanReason) < 5) {
            throw ValidationException::withMessages([
                'reason' => ['Please provide a valid reason (at least 5 characters) for your late arrival.'],
            ]);
        }

        $config = $this->policyService->getConfig();
        $extensionMinutes = $config['no_show_grace_extension_minutes'];

        /** @var Appointment $updated */
        $updated = DB::transaction(function () use ($appointment, $customer, $cleanReason, $extensionMinutes): Appointment {
            /** @var Appointment $locked */
            $locked = Appointment::with(['service', 'barber.user', 'customer'])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $locked->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'appointment_id' => ['You can only extend your own appointments.'],
                ]);
            }

            $eligibility = $this->policyService->canCustomerExtend($locked);
            if (! $eligibility['allowed']) {
                throw ValidationException::withMessages([
                    'reason' => [(string) $eligibility['reason']],
                ]);
            }

            $currentDeadline = $this->policyService->effectiveDeadline($locked);
            if ($currentDeadline === null) {
                throw ValidationException::withMessages([
                    'appointment_time' => ['Unable to compute appointment deadline.'],
                ]);
            }

            $newDeadline = $currentDeadline->copy()->addMinutes($extensionMinutes);
            $appTimezone = (string) (config('app.timezone') ?: 'UTC');

            $locked->update([
                'grace_extended_until' => $newDeadline->copy()->setTimezone($appTimezone),
                'grace_extension_count' => ((int) ($locked->grace_extension_count ?? 0)) + 1,
                'grace_extension_reason' => $cleanReason,
                // Reset warning timestamp so a final warning can be sent before the extended deadline if needed
                'no_show_warning_sent_at' => null,
            ]);

            AppointmentStatusHistory::create([
                'appointment_id' => $locked->id,
                'previous_status' => $locked->status->value,
                'new_status' => $locked->status->value,
                'changed_by_user_id' => $customer->id,
                'reason' => "Grace period extended (+{$extensionMinutes}m): {$cleanReason}",
                'created_at' => now(),
            ]);

            return $locked->fresh(['service.category', 'barber.user', 'serviceZone', 'customer', 'payment', 'noShowRecord']);
        }, 3);

        try {
            NotificationDispatcher::gracePeriodRequested($updated, $customer, $cleanReason, $extensionMinutes);
        } catch (\Throwable $e) {
            Log::warning("RequestGracePeriod notification failed: {$e->getMessage()}");
        }

        try {
            AuditLogger::log(
                'appointment.grace_extended',
                'Booking',
                $customer,
                [],
                ['extension_minutes' => $extensionMinutes, 'reason' => $cleanReason],
                Appointment::class,
                $updated->id
            );
        } catch (\Throwable) {
            // Non-fatal
        }

        return $updated;
    }
}
