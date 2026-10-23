<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserSuspended;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to user suspension — logs the action and could
 * notify the suspended user via email.
 */
class HandleUserSuspended implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(UserSuspended $event): void
    {
        $user = $event->user;
        $actorName = $event->actor?->name ?? 'System';
        $reason = $event->reason ?? 'No reason provided';

        Log::warning("User suspended: {$user->email} by {$actorName}. Reason: {$reason}", [
            'user_id' => $user->id,
            'actor_id' => $event->actor?->id,
            'reason' => $reason,
        ]);

        // Future: Send suspension notification email
    }
}
