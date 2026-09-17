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

class UpdateBarber
{

    public function execute(int $id, array $data): array
    {
        $barber = Barber::with('user')->findOrFail($id);

        $userUpdates = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'status']));
        if (!empty($data['password'])) {
            $userUpdates['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        if (!empty($userUpdates) && $barber->user) {
            $barber->user->update($userUpdates);
        }

        $barberUpdates = [];
        if (isset($data['bio'])) $barberUpdates['bio'] = $data['bio'];
        if (isset($data['experience_years'])) $barberUpdates['years_experience'] = $data['experience_years'];
        if (isset($data['status'])) $barberUpdates['status'] = $data['status'];
        if (isset($data['specialties'])) {
            $barberUpdates['specialties'] = is_string($data['specialties'])
                ? array_filter(array_map('trim', explode(',', $data['specialties'])))
                : $data['specialties'];
        }

        if (!empty($barberUpdates)) {
            $barber->update($barberUpdates);
        }

        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'email' => $barber->user?->email,
            'status' => $barber->status,
        ];
    }
}
