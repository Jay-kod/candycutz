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

class GetCustomerProfile
{

    public function execute(int $id): array
    {
        $customer = User::findOrFail($id);
        $appointments = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $customer->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'date' => $a->appointment_date,
                'time' => $a->appointment_time,
                'status' => $a->status,
                'service_name' => $a->service?->name ?? 'Custom Service',
                'barber_name' => $a->barber?->user?->name ?? 'Master Barber',
                'amount' => (float) $a->total_price,
            ]);

        $totalSpent = (float) Appointment::where('customer_id', $customer->id)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? 'N/A',
                'avatar' => $customer->avatar,
                'created_at' => $customer->created_at?->toIso8601String(),
            ],
            'total_bookings' => $appointments->count(),
            'total_spent' => $totalSpent,
            'appointments' => $appointments,
        ];
    }
}
