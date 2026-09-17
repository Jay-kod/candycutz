<?php

namespace App\Domain\System\Actions;

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

class GetReports
{

    public function execute(): array
    {
        return [
            'appointments_by_status' => Appointment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'services_by_category' => ServiceCategory::query()->withCount('services')->orderBy('display_order')->get(),
            'featured_barbers' => Barber::query()->where('is_featured', true)->with('user')->orderBy('display_order')->get(),
            'revenue_estimate' => Appointment::query()->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])->sum('total_price'),
        ];
    }
}
