<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

enum AppointmentPaymentStatus: string
{
    case unpaid = 'unpaid';
    case pay_at_venue = 'pay_at_venue';
    case partially_paid = 'partially_paid';
    case paid = 'paid';
    case refunded = 'refunded';
    case refund_pending = 'refund_pending';
}
