<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Enums\UserRole;
use App\Events\UserRegistered;
use App\Mail\AdminNewUserNotification;
use App\Mail\WelcomeCustomer;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Reacts to new user registrations — dispatches customer welcome email,
 * records in-app notifications, and alerts platform administrators.
 */
class HandleUserRegistered implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;
        $provider = $event->provider ?? 'email';

        Log::info("New user registered: {$user->email}", [
            'user_id' => $user->id,
            'role' => $user->role,
            'provider' => $provider,
        ]);

        // 1. Dispatch Customer Welcome Email via Resend
        if (! empty($user->email) && ! str_ends_with($user->email, '@social.candycutz.com')) {
            try {
                Mail::to($user->email)->send(new WelcomeCustomer($user));
            } catch (\Throwable $e) {
                Log::error("Failed sending welcome email to customer {$user->email}: ".$e->getMessage());
            }
        }

        // 2. Dispatch In-App Welcome Notification for Customer
        try {
            NotificationDispatcher::accountCreated($user);
        } catch (\Throwable $e) {
            Log::warning("Failed to record accountCreated notification for user {$user->id}: ".$e->getMessage());
        }

        // 3. Dispatch In-App / Push Notification to All Administrators
        try {
            NotificationDispatcher::newUserRegisteredAdmin($user, $provider);
        } catch (\Throwable $e) {
            Log::warning("Failed to record admin in-app notification for new user {$user->id}: ".$e->getMessage());
        }

        // 4. Dispatch Email Notification to All Administrators
        try {
            $adminEmails = User::whereIn('role', [UserRole::admin, UserRole::super_admin])
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->unique()
                ->toArray();

            if (! empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminNewUserNotification($user, $provider));
            }
        } catch (\Throwable $e) {
            Log::error("Failed sending admin new user alert email: ".$e->getMessage());
        }
    }
}
