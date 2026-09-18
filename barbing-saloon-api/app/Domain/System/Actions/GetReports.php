<?php

namespace App\Domain\System\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\ServiceCategory;

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
