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

class ApproveAppointment
{

    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::confirmed->value]);

        return $appointment->refresh()->load(['service', 'barber.user', 'customer']);
    }
}
