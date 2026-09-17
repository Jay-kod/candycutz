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

class DeleteGalleryItem
{
    use \App\Core\Traits\HasSecureUploads;

    public function execute(Gallery $gallery): void
    {
        if ($gallery->image_path) {
            $this->deleteFile($gallery->image_path);
        }

        $gallery->delete();
    }
}
