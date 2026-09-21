<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class GetAppointments
{
    /**
     * @return LengthAwarePaginator<Appointment>
     */
    public function execute(User $user, ?string $status = null, int $perPage = 15, ?AppointmentSource $source = null): LengthAwarePaginator
    {
        $role = $user->role?->value ?? $user->role;
        $query = Appointment::query()->with(['service.category', 'barber.user', 'serviceZone', 'customer']);

        if ($role === 'barber' && $user->barber) {
            $query->where('barber_id', $user->barber->id);
        } elseif ($role !== 'admin' && $role !== 'super_admin') {
            $query->where('customer_id', $user->id);
        }

        if ($source !== null) {
            $query->where('source', $source->value);
        }

        if ($status && $status !== 'all') {
            if ($status === 'upcoming') {
                $query->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
                    ->whereDate('appointment_date', '>=', now()->toDateString());
            } elseif ($status === 'completed') {
                $query->where('status', AppointmentStatus::completed->value);
            } elseif ($status === 'cancelled') {
                $query->where('status', AppointmentStatus::cancelled->value);
            } else {
                $query->where('status', $status);
            }
        }

        return $query->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate($perPage);
    }
}
