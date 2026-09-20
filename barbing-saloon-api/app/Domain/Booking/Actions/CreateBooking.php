<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Booking\Services\BookingService;
use App\Exceptions\BookingSlotUnavailableException;
use App\Models\Appointment;
use App\Models\User;

class CreateBooking
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Create a new customer appointment with slot locking.
     *
     * @throws BookingSlotUnavailableException
     */
    public function execute(User $customer, BookingData $data): Appointment
    {
        return $this->bookingService->createBooking($customer, $data);
    }
}