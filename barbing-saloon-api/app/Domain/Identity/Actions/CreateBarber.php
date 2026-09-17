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

class CreateBarber
{

    public function execute(array $data): array
    {
        $password = !empty($data['password']) ? $data['password'] : 'BarberPass123!';
        $email = $data['email'];

        $existing = User::where('email', $email)->first();
        if ($existing) {
            abort(409, 'A user with this email already exists');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'phone' => $data['phone'] ?? null,
            'role' => 'barber',
            'status' => $data['status'] ?? 'active',
            'is_active' => true,
        ]);

        $specialties = $data['specialties'] ?? [];
        if (is_string($specialties)) {
            $specialties = array_filter(array_map('trim', explode(',', $specialties)));
        }

        $barber = Barber::create([
            'user_id' => $user->id,
            'bio' => $data['bio'] ?? null,
            'specialties' => $specialties,
            'years_experience' => $data['experience_years'] ?? 3,
            'status' => $data['status'] ?? 'active',
            'is_available' => true,
            'rating' => 5.0,
        ]);

        for ($d = 0; $d < 7; $d++) {
            WorkingHour::create([
                'barber_id' => $barber->id,
                'day_of_week' => $d,
                'open_time' => '08:00:00',
                'close_time' => '19:00:00',
                'is_closed' => false,
            ]);
        }

        return [
            'id' => $barber->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $barber->status,
        ];
    }
}
