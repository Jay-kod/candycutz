<?php

namespace App\Modules\Customer\Services;

use App\Core\Enums\AppointmentStatus;
use App\Core\Traits\HasSecureUploads;
use App\Jobs\SendAdminNotification;
use App\Jobs\SendBookingConfirmation;
use App\Jobs\SendBookingCancellation;
use App\Exceptions\BookingSlotUnavailableException;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Modules\Landing\Services\SlotHelper;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    use HasSecureUploads;

    public function __construct(
        protected ?BookingService $bookingService = null
    ) {
        $this->bookingService = $bookingService ?? app(BookingService::class);
    }

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
        try {
            return $this->bookingService->createBooking($user, $data);
        } catch (BookingSlotUnavailableException $e) {
            abort(response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [
                    'code' => 'BOOKING_SLOT_UNAVAILABLE',
                    'message' => $e->getMessage(),
                    'details' => ['slot' => [$e->getSlotDetails()]],
                ],
                'code' => 409,
            ], 409));
        }
    }

    public function cancelBooking(Appointment $appointment, ?User $actor = null, ?string $reason = 'Customer requested cancellation'): Appointment
    {
        $currentStatus = $appointment->status?->value ?? (string) $appointment->status;
        if (! in_array($currentStatus, [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value], true)) {
            abort(422, 'Booking cannot be cancelled');
        }

        $user = $actor ?? $appointment->customer ?? User::find($appointment->customer_id);
        if ($user) {
            $this->bookingService->transitionStatus($appointment, AppointmentStatus::cancelled->value, $user, $reason);
        } else {
            $appointment->update(['status' => AppointmentStatus::cancelled->value]);
        }

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
                ->with(['barber.user'])
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
            ->with(['barber.user'])
            ->where('customer_id', $user->id)
            ->latest()
            ->paginate(10);
    }

    public function createReview(User $user, array $data): Testimonial
    {
        $content = $data['review'] ?? $data['comment'] ?? '';

        return Testimonial::query()->create([
            'customer_id' => $user->id,
            'client_name' => $user->name,
            'client_avatar' => $user->avatar,
            'rating' => (int) $data['rating'],
            'review' => $content,
            'service_id' => $data['service_id'] ?? null,
            'barber_id' => $data['barber_id'] ?? null,
            'is_approved' => false,
            'is_featured' => false,
        ]);
    }
}