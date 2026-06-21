<?php

namespace App\Core\Enums;

enum AppointmentStatus: string
{
    case pending = 'pending';
    case confirmed = 'confirmed';
    case completed = 'completed';
    case cancelled = 'cancelled';
    case no_show = 'no_show';
}