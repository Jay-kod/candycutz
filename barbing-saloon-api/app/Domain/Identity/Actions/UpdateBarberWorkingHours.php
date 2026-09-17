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

class UpdateBarberWorkingHours
{

    public function execute(int $barberId, array $hours): array
    {
        $barber = Barber::findOrFail($barberId);
        foreach ($hours as $h) {
            if (!isset($h['day_of_week'])) {
                continue;
            }

            WorkingHour::updateOrCreate(
                ['barber_id' => $barber->id, 'day_of_week' => (int) $h['day_of_week']],
                [
                    'open_time' => $h['open_time'] ?? '09:00:00',
                    'close_time' => $h['close_time'] ?? '18:00:00',
                    'is_closed' => isset($h['is_closed']) ? (bool) $h['is_closed'] : false,
                ]
            );
        }

        return $this->workingHours();
    }
}
