<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\NoShowPolicyService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\NoShowRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ResolveNoShowAppeal
{
    public function __construct(
        protected NoShowPolicyService $policyService
    ) {}

    public function execute(
        NoShowRecord $record,
        User $admin,
        string $outcome,
        ?string $note = null
    ): NoShowRecord {
        if (! in_array($outcome, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'outcome' => ["Invalid appeal outcome '{$outcome}'."],
            ]);
        }

        $updated = DB::transaction(function () use ($record, $admin, $outcome, $note): NoShowRecord {
            /** @var NoShowRecord $locked */
            $locked = NoShowRecord::with(['appointment', 'customer'])
                ->whereKey($record->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->appealed || $locked->appeal_outcome !== 'pending') {
                throw ValidationException::withMessages([
                    'outcome' => ['Only pending appeals can be resolved.'],
                ]);
            }

            $now = now();
            $locked->update([
                'appeal_outcome' => $outcome,
                'resolved_by' => $admin->id,
                'resolved_at' => $now,
                'resolution_note' => $note ? trim($note) : null,
            ]);

            if ($outcome === 'approved') {
                if ($locked->deposit_forfeited && $locked->appointment) {
                    $locked->update([
                        'deposit_forfeited' => false,
                        'deposit_forfeited_minor' => 0,
                    ]);
                    $locked->appointment->update([
                        'deposit_forfeited' => false,
                    ]);
                }

                if ($locked->customer_id) {
                    /** @var User|null $customer */
                    $customer = User::whereKey($locked->customer_id)->lockForUpdate()->first();
                    if ($customer) {
                        $config = $this->policyService->getConfig();
                        $rolling = $this->policyService->rollingNoShowCount(
                            $customer->id,
                            $config['no_show_threshold_window_days']
                        );

                        $customer->forceFill([
                            'no_show_count' => max(0, ((int) $customer->no_show_count) - 1),
                            'requires_deposit' => $rolling >= $config['no_show_threshold'],
                        ])->save();
                    }
                }
            }

            return $locked->fresh(['appointment.service', 'appointment.barber.user', 'appointment.payment', 'customer', 'resolvedBy']);
        }, 3);

        try {
            NotificationDispatcher::noShowAppealResolved($updated);
        } catch (\Throwable $e) {
            Log::warning("ResolveNoShowAppeal notification failed: {$e->getMessage()}");
        }

        try {
            AuditLogger::log(
                "no_show.appeal_{$outcome}",
                'Booking',
                $admin,
                ['appeal_outcome' => 'pending'],
                ['appeal_outcome' => $outcome, 'note' => $note],
                NoShowRecord::class,
                $updated->id
            );
        } catch (\Throwable) {
            // Non-fatal
        }

        return $updated;
    }
}
