<?php

namespace App\Domain\Shared\Enums;

enum AppointmentStatus: string
{
    case pending = 'pending';
    case confirmed = 'confirmed';
    case completed = 'completed';
    case cancelled = 'cancelled';
    case no_show = 'no_show';
}
