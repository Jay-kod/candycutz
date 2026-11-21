<?php

declare(strict_types=1);

namespace App\Domain\Notification\Contracts;

use App\Models\Notification;
use App\Models\User;

interface NotificationChannel
{
    /**
     * Send a notification to a specific user through this channel.
     *
     * @param  array<string, mixed>  $context
     */
    public function send(User $recipient, Notification $notification, array $context = []): bool;

    /**
     * Whether this channel is enabled for the recipient based on preferences or system rules.
     */
    public function isEnabledFor(User $recipient, string $type): bool;
}
