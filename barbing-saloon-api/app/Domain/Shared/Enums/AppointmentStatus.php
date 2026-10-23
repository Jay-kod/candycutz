<?php

namespace App\Domain\Shared\Enums;

enum AppointmentStatus: string
{
    case pending = 'pending';
    case confirmed = 'confirmed';
    case checked_in = 'checked_in';
    case in_progress = 'in_progress';
    case completed = 'completed';
    case cancelled = 'cancelled';
    case no_show = 'no_show';

    /**
     * Statuses considered "active" (not terminal).
     */
    public static function activeStatuses(): array
    {
        return [
            self::pending,
            self::confirmed,
            self::checked_in,
            self::in_progress,
        ];
    }

    /**
     * Statuses considered "terminal" (no further transitions possible).
     */
    public static function terminalStatuses(): array
    {
        return [
            self::completed,
            self::cancelled,
            self::no_show,
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, self::terminalStatuses(), true);
    }
}
