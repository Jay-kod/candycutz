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

class GetWorkingHours
{

    public function execute(): array
    {
        $barbers = Barber::with(['user', 'workingHours' => fn ($q) => $q->orderBy('day_of_week')])->get();

        return $barbers->map(function ($b) {
            return [
                'barber_id' => $b->id,
                'barber_name' => $b->user?->name ?? 'Barber #' . $b->id,
                'hours' => $b->workingHours->map(fn ($h) => [
                    'id' => $h->id,
                    'day_of_week' => $h->day_of_week,
                    'open_time' => substr($h->open_time, 0, 5),
                    'close_time' => substr($h->close_time, 0, 5),
                    'is_closed' => (bool) $h->is_closed,
                ])->values()->all(),
            ];
        })->values()->all();
    }
}
