<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to new user registrations — sends welcome email
 * and logs the registration for analytics.
 */
class HandleUserRegistered implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        Log::info("New user registered: {$user->email}", [
            'user_id' => $user->id,
            'role' => $user->role,
        ]);

        // Future: Send welcome email via BrevoNotificationAdapter
        // Future: Track analytics event
    }
}
