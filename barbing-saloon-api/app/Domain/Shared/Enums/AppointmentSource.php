<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

enum AppointmentSource: string
{
    case web = 'web';
    case app = 'app';
    case walk_in = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::web => 'Website',
            self::app => 'Mobile App',
            self::walk_in => 'Walk-In',
        };
    }
}