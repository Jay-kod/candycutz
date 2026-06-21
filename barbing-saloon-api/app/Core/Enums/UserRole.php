<?php

namespace App\Core\Enums;

enum UserRole: string
{
    case super_admin = 'super_admin';
    case admin = 'admin';
    case barber = 'barber';
    case customer = 'customer';
}