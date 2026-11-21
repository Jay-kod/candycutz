<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PasswordChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to password changes — logs the event for security
 * audit and can notify the user via email.
 */
class HandlePasswordChanged implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(PasswordChanged $event): void
    {
        $user = $event->user;

        Log::info("Password changed for user: {$user->email}", [
            'user_id' => $user->id,
        ]);

        // Future: Send "your password was changed" security notification email
    }
}
