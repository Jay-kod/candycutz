<?php

namespace App\Domain\Catalogue\Actions;

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

class CreateService
{
    use \App\Core\Traits\HasSecureUploads;

    public function execute(array $data): Service
    {
        $imagePath = isset($data['image']) && $data['image'] instanceof UploadedFile
            ? $this->uploadFile($data['image'], 'services')
            : null;

        return Service::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'category_id' => $data['category_id'] ?? 1, // fallback
            'image' => $imagePath,
            'is_available' => $data['is_available'] ?? $data['is_active'] ?? true,
        ]);
    }
}
