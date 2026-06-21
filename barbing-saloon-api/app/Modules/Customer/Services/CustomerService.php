<?php

namespace App\Modules\Customer\Services;

use App\Core\Enums\AppointmentStatus;
use App\Core\Traits\HasSecureUploads;
use App\Jobs\SendAdminNotification;
use App\Jobs\SendBookingConfirmation;
use App\Jobs\SendBookingCancellation;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Modules\Landing\Services\SlotHelper;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    use HasSecureUploads;

    public function dashboard(User $user): array
    {
        $upcoming = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(3)
            ->get();

        return [
            'stats' => [
                'total_bookings' => Appointment::query()->where('customer_id', $user->id)->count(),
                'upcoming_bookings' => Appointment::query()
                    ->where('customer_id', $user->id)
                    ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
                    ->whereDate('appointment_date', '>=', now()->toDateString())
                    ->count(),
                'completed_bookings' => Appointment::query()->where('customer_id', $user->id)->where('status', AppointmentStatus::completed->value)->count(),
                'reviews_count' => Testimonial::query()->where('customer_id', $user->id)->count(),
            ],
            'upcoming_appointments' => $upcoming,
            'profile' => $this->profile($user),
        ];
    }

    public function bookings(User $user): LengthAwarePaginator
    {
        return Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(10);
    }

    public function booking(User $user, int $appointmentId): ?Appointment
    {
        return Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->find($appointmentId);
    }

    public function createBooking(User $user, array $data): Appointment
    {
        $barber = Barber::query()->findOrFail($data['barber_id']);
        $service = Service::query()->findOrFail($data['service_id']);
        $date = Carbon::parse($data['appointment_date']);

        $slotHelper = new SlotHelper();
        $slots = $slotHelper->generate($date, $barber, $service);

        if (! in_array($data['appointment_time'], $slots, true)) {
            abort(422, 'Selected time is not available');
        }

        $depositAmount = (float) Setting::query()->where('key', 'deposit_amount')->value('value') ?: 0;

        $appointment = Appointment::query()->create([
            'customer_id' => $user->id,
            'client_name' => $user->name,
            'client_phone' => $user->phone ?? '',
            'client_email' => $user->email,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => $date->toDateString(),
            'appointment_time' => $data['appointment_time'],
            'status' => AppointmentStatus::pending->value,
            'notes' => $data['notes'] ?? null,
            'total_price' => $service->price,
            'deposit_paid' => false,
            'deposit_amount' => $depositAmount,
        ]);

        // Dispatch email jobs
        SendBookingConfirmation::dispatch($appointment);
        SendAdminNotification::dispatch($appointment, 'new_booking');

        return $appointment;
    }

    public function cancelBooking(Appointment $appointment): Appointment
    {
        // Dispatch cancellation email and notification
        SendBookingCancellation::dispatch($appointment);
        SendAdminNotification::dispatch($appointment, 'cancellation');

        if (! in_array($appointment->status?->value ?? $appointment->status, [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value], true)) {
            abort(422, 'Booking cannot be cancelled');
        }

        $appointment->update(['status' => AppointmentStatus::cancelled->value]);

        return $appointment->refresh()->load(['service', 'barber.user']);
    }

    public function profile(User $user): array
    {
        return [
            'user' => $user->loadCount(['appointments', 'testimonials']),
            'latest_bookings' => Appointment::query()
                ->with(['service', 'barber.user'])
                ->where('customer_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
            'recent_reviews' => Testimonial::query()
                ->with(['service', 'barber.user'])
                ->where('customer_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            $data['avatar'] = $this->uploadFile($avatar, $this->buildUserPath('avatars', $user->id));
        }

        $user->update($data);

        return $user->refresh();
    }

    public function reviews(User $user): LengthAwarePaginator
    {
        return Testimonial::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->latest()
            ->paginate(10);
    }

    public function createReview(User $user, array $data): Testimonial
    {
        return Testimonial::query()->create([
            'customer_id' => $user->id,
            'client_name' => $user->name,
            'client_avatar' => $user->avatar,
            'rating' => $data['rating'],
            'review' => $data['review'],
            'service_id' => $data['service_id'],
            'barber_id' => $data['barber_id'],
            'is_approved' => false,
            'is_featured' => false,
        ]);
    }
}