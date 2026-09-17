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

class UpdateService
{
    use \App\Core\Traits\HasSecureUploads;

    public function execute(Service $service, array $data): Service
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($service->image) {
                $this->deleteFile($service->image);
            }

            $data['image'] = $this->uploadFile($data['image'], 'services');
        } else {
            unset($data['image']);
        }
        
        unset($data['slug']);
        unset($data['is_active']);
        unset($data['display_order']);

        $service->update($data);

        return $service->refresh()->load('category');
    }
}
