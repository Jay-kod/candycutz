<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;

class GetCustomers
{
    public function execute(): array
    {
        return User::query()
            ->where('role', 'customer')
            ->withCount('appointments as total_bookings')
            ->get()
            ->map(function ($u) {
                $totalSpent = (float) Appointment::where('customer_id', $u->id)
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                $lastBooking = Appointment::where('customer_id', $u->id)
                    ->latest('appointment_date')
                    ->value('appointment_date');

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?? 'N/A',
                    'avatar' => $u->avatar,
                    'created_at' => $u->created_at?->toIso8601String(),
                    'total_bookings' => (int) $u->total_bookings,
                    'total_spent' => $totalSpent,
                    'last_booking_date' => $lastBooking,
                ];
            })
            ->all();
    }
}
