<?php

namespace App\Domain\Booking\Actions;

use App\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAppointments
{
    public function execute(): LengthAwarePaginator
    {
        return Appointment::query()->with(['service', 'barber.user', 'customer'])->latest('appointment_date')->latest('appointment_time')->paginate(15);
    }
}
