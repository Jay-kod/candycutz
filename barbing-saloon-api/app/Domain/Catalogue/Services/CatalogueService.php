<?php

declare(strict_types=1);

namespace App\Domain\Catalogue\Services;

use App\Models\Barber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\WorkingHour;
use App\Services\SlotHelper;
use Carbon\Carbon;

class CatalogueService
{
    public function services(): array
    {
        return Service::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => $this->serviceData($service))
            ->all();
    }

    public function serviceBySlug(string $slug): ?array
    {
        $service = Service::query()->with('category')->find($slug);

        return $service ? $this->serviceData($service) : null;
    }

    public function serviceCategories(): array
    {
        return ServiceCategory::query()
            ->withCount(['services as services_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon,
                'services_count' => $category->services_count,
            ])
            ->all();
    }

    public function barbers(): array
    {
        return Barber::query()
            ->with(['user'])
            ->where('is_available', true)
            ->orderBy('rating', 'desc')
            ->get()
            ->map(fn (Barber $barber) => $this->barberData($barber))
            ->all();
    }

    public function barberById(int $id): ?array
    {
        $barber = Barber::query()->with(['user'])->find($id);

        return $barber ? $this->barberData($barber) : null;
    }

    public function workingHours(): array
    {
        return WorkingHour::query()
            ->with('barber.user')
            ->orderBy('day_of_week')
            ->orderBy('open_time')
            ->get()
            ->map(fn (WorkingHour $hour) => [
                'id' => $hour->id,
                'barber_id' => $hour->barber_id,
                'day_of_week' => $hour->day_of_week,
                'open_time' => $hour->open_time,
                'close_time' => $hour->close_time,
                'is_closed' => $hour->is_closed,
                'barber' => $hour->barber ? $this->barberData($hour->barber) : null,
            ])
            ->all();
    }

    public function availableSlots(Carbon $date, int $barberId, int $serviceId): array
    {
        $barber = Barber::query()->find($barberId);
        $service = Service::query()->find($serviceId);

        if (! $barber || ! $service) {
            return [];
        }

        $slotHelper = new SlotHelper;

        return $slotHelper->generate($date, $barber, $service);
    }

    protected function barberData(Barber $barber): array
    {
        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'avatar' => $barber->user?->avatar,
            'bio' => $barber->bio,
            'specialties' => $barber->specialties ?? [],
            'experience_years' => $barber->experience_years,
            'rating' => $barber->rating,
            'is_available' => $barber->is_available,
        ];
    }

    protected function serviceData(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => $service->price,
            'duration_minutes' => $service->duration_minutes,
            'image' => $service->image,
            'is_available' => (bool) $service->is_active,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
            ] : null,
        ];
    }
}
