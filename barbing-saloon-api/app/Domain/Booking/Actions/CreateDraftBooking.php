<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Shared\Enums\AppointmentSource;
use App\Models\Appointment;
use App\Models\User;

class CreateDraftBooking
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Create a new draft booking with 15-minute slot hold.
     */
    public function execute(User $customer, BookingData $data, AppointmentSource $source = AppointmentSource::web): Appointment
    {
        return $this->bookingService->createDraftBooking($customer, $data, $source);
    }
}
