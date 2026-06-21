<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $appointment->customer_id === $user->id;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $appointment->customer_id === $user->id;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $appointment->customer_id === $user->id;
    }

    public function manageForBarber(User $user, Appointment $appointment): bool
    {
        return $appointment->barber?->user_id === $user->id;
    }

    public function complete(User $user, Appointment $appointment): bool
    {
        return $appointment->barber?->user_id === $user->id;
    }

    public function markNoShow(User $user, Appointment $appointment): bool
    {
        return $appointment->barber?->user_id === $user->id;
    }
}