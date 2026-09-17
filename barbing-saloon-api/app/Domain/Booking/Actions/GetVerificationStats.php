<?php

namespace App\Domain\Booking\Actions;

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

class GetVerificationStats
{

    public function execute(): array
    {
        $total = Appointment::query()->count();
        $verifiedToday = Appointment::query()
            ->where('status', AppointmentStatus::completed->value)
            ->whereDate('updated_at', today())
            ->count();
        $pending = Appointment::query()
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->whereDate('appointment_date', '>=', today())
            ->count();
        $expired = Appointment::query()
            ->where('appointment_date', '<', today())
            ->where('status', '!=', AppointmentStatus::completed->value)
            ->count();

        return [
            'total' => $total,
            'verified_today' => $verifiedToday,
            'pending' => $pending,
            'expired' => $expired,
        ];
    }
}
