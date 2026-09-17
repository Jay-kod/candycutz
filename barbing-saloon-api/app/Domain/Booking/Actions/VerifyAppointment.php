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

class VerifyAppointment
{

    public function execute(int $id): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => AppointmentStatus::completed->value,
            'deposit_paid' => true,
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            \Illuminate\Support\Facades\DB::table('payments')
                ->where('appointment_id', $appointment->id)
                ->update(['status' => 'successful', 'updated_at' => now()]);
        }

        return $appointment;
    }
}
