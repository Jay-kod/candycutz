<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;

class GetVerifications
{
    public function execute(array $filters): array
    {
        $query = Appointment::query()->with(['customer', 'barber.user', 'service']);

        if (! empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($sub) use ($q) {
                $sub->where('booking_reference', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
            });
        }

        $filter = $filters['filter'] ?? 'all';
        if ($filter === 'confirmed' || $filter === 'pending') {
            $query->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value]);
        } elseif ($filter === 'completed') {
            $query->where('status', AppointmentStatus::completed->value);
        } elseif ($filter === 'expired') {
            $query->where('appointment_date', '<', today()->toDateString())->where('status', '!=', AppointmentStatus::completed->value);
        }

        return $query->latest('appointment_date')->latest('appointment_time')->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'verification_code' => $a->verification_code,
                'status' => $a->status instanceof AppointmentStatus ? $a->status->value : $a->status,
                'appointment_date' => $a->appointment_date?->toDateString() ?? $a->appointment_date,
                'appointment_time' => $a->appointment_time,
                'total_price' => (float) $a->total_price,
                'customer_name' => $a->customer?->name ?? $a->client_name ?? 'Client',
                'customer_phone' => $a->customer?->phone ?? $a->client_phone ?? 'N/A',
                'customer_email' => $a->customer?->email ?? $a->client_email ?? '',
                'barber_name' => $a->barber?->user?->name ?? 'Master Barber',
                'service_name' => $a->service?->name ?? 'Service',
            ];
        })->all();
    }
}
