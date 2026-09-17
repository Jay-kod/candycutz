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

class UpdateBarberStatus
{

    public function execute(int $id, string $status): array
    {
        $barber = Barber::with('user')->findOrFail($id);
        $barber->update(['status' => $status]);
        if ($barber->user) {
            $barber->user->update(['status' => $status]);
        }

        return ['id' => $barber->id, 'status' => $status];
    }
}
