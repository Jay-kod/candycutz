<?php

namespace App\Modules\Barber\Services;

use App\Core\Enums\AppointmentStatus;
use App\Jobs\SendAdminNotification;
use App\Jobs\SendBookingThankYou;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\WorkingHour;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BarberService
{
    public function barberForUser(User $user): Barber
    {
        return Barber::query()->with('user')->where('user_id', $user->id)->firstOrFail();
    }

    public function dashboard(User $user): array
    {
        $barber = $this->barberForUser($user);

        return [
            'profile' => $barber,
            'stats' => [
                'today_bookings' => Appointment::query()->where('barber_id', $barber->id)->whereDate('appointment_date', today())->count(),
                'upcoming_bookings' => Appointment::query()->where('barber_id', $barber->id)->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])->whereDate('appointment_date', '>=', today())->count(),
                'completed_bookings' => Appointment::query()->where('barber_id', $barber->id)->where('status', AppointmentStatus::completed->value)->count(),
                'no_show_count' => Appointment::query()->where('barber_id', $barber->id)->where('status', AppointmentStatus::no_show->value)->count(),
            ],
            'today_appointments' => Appointment::query()
                ->with(['service', 'customer'])
                ->where('barber_id', $barber->id)
                ->whereDate('appointment_date', today())
                ->orderBy('appointment_time')
                ->get(),
            'upcoming_appointments' => Appointment::query()
                ->with(['service', 'customer'])
                ->where('barber_id', $barber->id)
                ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
                ->whereDate('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(5)
                ->get(),
        ];
    }

    public function schedule(User $user): array
    {
        $barber = $this->barberForUser($user);

        return [
            'working_hours' => WorkingHour::query()->where('barber_id', $barber->id)->orderBy('day_of_week')->get(),
            'appointments' => Appointment::query()
                ->with(['service', 'customer'])
                ->where('barber_id', $barber->id)
                ->whereDate('appointment_date', '>=', today()->toDateString())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get(),
        ];
    }

    public function appointments(User $user): LengthAwarePaginator
    {
        $barber = $this->barberForUser($user);

        return Appointment::query()
            ->with(['service', 'customer'])
            ->where('barber_id', $barber->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->paginate(10);
    }

    public function complete(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::completed->value]);

        // Dispatch thank you email and admin notification
        SendBookingThankYou::dispatch($appointment);
        SendAdminNotification::dispatch($appointment, 'completed');

        return $appointment->refresh()->load(['service', 'customer']);
    }

    public function noShow(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::no_show->value]);

        // Dispatch admin notification
        SendAdminNotification::dispatch($appointment, 'no_show');

        return $appointment->refresh()->load(['service', 'customer']);
    }

    public function profile(User $user): Barber
    {
        return $this->barberForUser($user)->load('user');
    }

    public function updateProfile(User $user, array $data): Barber
    {
        $barber = $this->barberForUser($user);

        $barber->update(array_intersect_key($data, array_flip(['bio', 'specialties', 'years_experience', 'instagram_url'])));
        $barber->user->update(array_intersect_key($data, array_flip(['name', 'phone'])));

        return $barber->refresh()->load('user');
    }
}