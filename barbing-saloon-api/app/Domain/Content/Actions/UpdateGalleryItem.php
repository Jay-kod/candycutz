<?php

namespace App\Domain\Content\Actions;

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

class UpdateGalleryItem
{
    use \App\Core\Traits\HasSecureUploads;

    public function execute(Gallery $gallery, array $data): Gallery
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($gallery->image_path) {
                $this->deleteFile($gallery->image_path);
            }

            $data['image_path'] = $this->uploadFile($data['image'], 'gallery');
        }

        unset($data['image']);
        $gallery->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? $gallery->image_path,
            'category' => $data['category'],
            'barber_id' => $data['barber_id'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
        ]);

        return $gallery->refresh();
    }
}
