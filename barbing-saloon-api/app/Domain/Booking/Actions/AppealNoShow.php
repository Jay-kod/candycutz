<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Notification\NotificationDispatcher;
use App\Models\NoShowRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppealNoShow
{
    public function execute(NoShowRecord $record, User $customer, string $appealReason): NoShowRecord
    {
        $cleanReason = trim($appealReason);
        if (mb_strlen($cleanReason) < 5) {
            throw ValidationException::withMessages([
                'appeal_reason' => ['Please explain why you could not make the appointment (at least 5 characters).'],
            ]);
        }

        $updated = DB::transaction(function () use ($record, $customer, $cleanReason): NoShowRecord {
            /** @var NoShowRecord $locked */
            $locked = NoShowRecord::with(['appointment', 'customer'])
                ->whereKey($record->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $locked->customer_id !== (int) $customer->id) {
                abort(403, 'You can only appeal your own no-show record.');
            }

            if ($locked->appealed) {
                throw ValidationException::withMessages([
                    'appeal_reason' => ['An appeal has already been submitted for this no-show.'],
                ]);
            }

            $locked->update([
                'appealed' => true,
                'appeal_reason' => $cleanReason,
                'appealed_at' => now(),
                'appeal_outcome' => 'pending',
            ]);

            return $locked->fresh(['appointment.service', 'appointment.barber.user', 'appointment.payment', 'customer']);
        }, 3);

        try {
            NotificationDispatcher::noShowAppealed($updated, $customer);
        } catch (\Throwable $e) {
            Log::warning("AppealNoShow notification failed: {$e->getMessage()}");
        }

        return $updated;
    }
}
