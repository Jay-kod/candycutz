<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Shared\Enums\AppointmentSource;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\User;

class CreateWalkInAppointment
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Create a new walk-in appointment with slot locking.
     *
     * @throws \App\Exceptions\BookingSlotUnavailableException
     */
    public function execute(User $actor, Barber $barber, BookingData $data): Appointment
    {
        return $this->bookingService->createWalkIn($actor, $barber, $data, AppointmentSource::walk_in);
    }
}
