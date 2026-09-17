<?php

namespace App\Domain\Identity\Actions;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WorkingHour;
use App\Models\User;
use App\Core\Enums\AppointmentStatus;
use App\Core\Enums\BlogStatus;
use App\Core\Enums\GalleryCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

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
