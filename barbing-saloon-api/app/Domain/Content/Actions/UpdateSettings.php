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

class UpdateSettings
{
    use \App\Core\Traits\HasSecureUploads;

    public function __construct(protected GetSettings $getSettings) {}

    public function execute(array $settings, ?UploadedFile $heroImage = null): array
    {
        foreach ($settings as $key => $value) {
            $group = Setting::resolveGroupForKey((string) $key);
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        if ($heroImage) {
            $setting = Setting::query()->firstOrCreate(['key' => 'hero_image'], ['group' => 'hero']);
            if ($setting->value) {
                $this->deleteFile($setting->value);
            }
            
            $path = $this->uploadFile($heroImage, 'settings');
            $setting->update(['value' => $path]);
        }

        return $this->getSettings->execute();
    }
}
