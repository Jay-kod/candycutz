<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\AppointmentStateMachine;
use App\Domain\Booking\Services\NoShowPolicyService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Services\AuditLogger;
use App\Events\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\NoShowRecord;
use App\Models\User;
use App\Support\Errors\AuthException;
use App\Support\Errors\BookingException;
use App\Support\Errors\ErrorCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkNoShow
{
    public function __construct(
        protected NoShowPolicyService $policyService
    ) {}

    /**
     * Mark an appointment as a no-show (manual by staff or automatic by scheduler).
     */
    public function execute(
        Appointment $appointment,
        ?User $actor = null,
        ?string $reason = null,
        bool $automatic = false,
        ?bool $forfeitDeposit = null
    ): NoShowRecord {
        $config = $this->policyService->getConfig();
        $shouldForfeit = $forfeitDeposit ?? ($automatic && $config['no_show_auto_forfeit_deposit']);

        /** @var array{record: NoShowRecord, appointment: Appointment, previous_status: string, already_marked: bool, threshold_customer: User|null, rolling_count: int} $txResult */
        $txResult = DB::transaction(function () use ($appointment, $actor, $reason, $automatic, $shouldForfeit, $config): array {
            /** @var Appointment $locked */
            $locked = Appointment::with(['payment', 'customer', 'noShowRecord'])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $locked->status instanceof AppointmentStatus
                ? $locked->status->value
                : (string) $locked->status;

            if ($previousStatus === AppointmentStatus::no_show->value && $locked->noShowRecord) {
                return [
                    'record' => $locked->noShowRecord,
                    'appointment' => $locked,
                    'previous_status' => $previousStatus,
                    'already_marked' => true,
                    'threshold_customer' => null,
                    'rolling_count' => 0,
                ];
            }

            if (! AppointmentStateMachine::isAllowed($previousStatus, AppointmentStatus::no_show->value)) {
                throw BookingException::invalidTransition(
                    "Cannot mark appointment as no-show from '{$previousStatus}'.",
                    [
                        'appointment_id' => $locked->id,
                        'current_status' => $previousStatus,
                        'requested_status' => AppointmentStatus::no_show->value,
                    ]
                );
            }

            if ($actor !== null) {
                $role = $actor->role instanceof UserRole ? $actor->role->value : (string) ($actor->role ?? 'customer');
                if (! AppointmentStateMachine::isRoleAllowed($previousStatus, AppointmentStatus::no_show->value, $role)) {
                    throw new AuthException(
                        ErrorCode::AUTH_FORBIDDEN_ROLE,
                        "Role '{$role}' is not authorized to mark appointment as no-show."
                    );
                }
            }

            $now = now();
            $finalReason = trim((string) ($reason ?: ($automatic
                ? 'Automatically marked no-show after arrival deadline elapsed.'
                : 'Marked no-show by staff.')));

            $forfeitMinor = 0;
            $depositForfeited = false;
            if ($shouldForfeit && $locked->deposit_paid) {
                $depositForfeited = true;
                $forfeitMinor = $this->policyService->resolveDepositMinor($locked);
            }

            $locked->update([
                'status' => AppointmentStatus::no_show->value,
                'no_show_marked_at' => $now,
                'no_show_marked_by' => $actor?->id,
                'no_show_auto' => $automatic,
                'deposit_forfeited' => $depositForfeited,
                'deposit_forfeited_at' => $depositForfeited ? $now : null,
                'deposit_forfeited_by' => $depositForfeited ? $actor?->id : null,
            ]);

            AppointmentStatusHistory::create([
                'appointment_id' => $locked->id,
                'previous_status' => $previousStatus,
                'new_status' => AppointmentStatus::no_show->value,
                'changed_by_user_id' => $actor?->id,
                'reason' => $finalReason,
                'created_at' => $now,
            ]);

            $record = NoShowRecord::create([
                'appointment_id' => $locked->id,
                'customer_id' => $locked->customer_id,
                'marked_at' => $now,
                'marked_by' => $actor?->id,
                'reason' => $finalReason,
                'was_automatic' => $automatic,
                'deposit_forfeited' => $depositForfeited,
                'deposit_forfeited_minor' => $forfeitMinor,
                'deposit_forfeited_at' => $depositForfeited ? $now : null,
                'deposit_forfeited_by' => $depositForfeited ? $actor?->id : null,
                'grace_extended' => ((int) ($locked->grace_extension_count ?? 0)) > 0,
                'grace_reason' => $locked->grace_extension_reason,
            ]);

            $thresholdCustomer = null;
            $rollingCount = 0;
            if ($locked->customer_id) {
                /** @var User|null $customer */
                $customer = User::whereKey($locked->customer_id)->lockForUpdate()->first();
                if ($customer) {
                    $rollingCount = $this->policyService->rollingNoShowCount(
                        $customer->id,
                        $config['no_show_threshold_window_days']
                    );
                    $wasRequiringDeposit = (bool) $customer->requires_deposit;
                    $nowRequiresDeposit = $wasRequiringDeposit || $rollingCount >= $config['no_show_threshold'];

                    $customer->forceFill([
                        'no_show_count' => ((int) $customer->no_show_count) + 1,
                        'last_no_show_at' => $now,
                        'requires_deposit' => $nowRequiresDeposit,
                    ])->save();

                    if (! $wasRequiringDeposit && $nowRequiresDeposit) {
                        $thresholdCustomer = $customer->fresh();
                    }
                }
            }

            return [
                'record' => $record->fresh(['appointment.service', 'appointment.barber.user', 'appointment.payment', 'customer']),
                'appointment' => $locked->fresh(['service', 'barber.user', 'customer', 'payment', 'noShowRecord']),
                'previous_status' => $previousStatus,
                'already_marked' => false,
                'threshold_customer' => $thresholdCustomer,
                'rolling_count' => $rollingCount,
            ];
        }, 3);

        if (! $txResult['already_marked']) {
            AppointmentStatusChanged::dispatch(
                $txResult['appointment'],
                $txResult['previous_status'],
                AppointmentStatus::no_show->value,
                $actor,
                $reason
            );

            if ($txResult['threshold_customer'] !== null) {
                try {
                    NotificationDispatcher::noShowThresholdReached(
                        $txResult['threshold_customer'],
                        $txResult['rolling_count'],
                        $config['no_show_threshold']
                    );
                } catch (\Throwable $e) {
                    Log::warning("MarkNoShow threshold notification failed: {$e->getMessage()}");
                }
            }

            try {
                AuditLogger::log(
                    'appointment.no_show_marked',
                    'Booking',
                    $actor,
                    ['status' => $txResult['previous_status']],
                    ['status' => AppointmentStatus::no_show->value, 'automatic' => $automatic, 'deposit_forfeited' => $txResult['record']->deposit_forfeited],
                    Appointment::class,
                    $txResult['appointment']->id
                );
            } catch (\Throwable) {
                // Non-fatal audit logging failure
            }
        }

        return $txResult['record'];
    }
}
