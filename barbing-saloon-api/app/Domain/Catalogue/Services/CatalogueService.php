<?php

declare(strict_types=1);

namespace App\Domain\Catalogue\Services;

use App\Domain\Booking\Services\SlotHelper;
use App\Domain\Shared\Services\CatalogueCache;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Barber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\WorkingHour;
use Carbon\Carbon;

class CatalogueService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function services(): array
    {
        return CatalogueCache::remember('services', 'all', function () {
            return Service::query()
                ->with('category')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Service $service) => $this->serviceData($service))
                ->all();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function serviceBySlug(string $slug): ?array
    {
        return CatalogueCache::remember('services', "slug:{$slug}", function () use ($slug) {
            $service = Service::query()->with('category')->find($slug);

            return $service ? $this->serviceData($service) : null;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function serviceCategories(): array
    {
        return CatalogueCache::remember('categories', 'all', function () {
            return ServiceCategory::query()
                ->withCount(['services as services_count' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('name')
                ->get()
                ->map(/** @return array<string, mixed> */ fn (ServiceCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'services_count' => $category->services_count,
                ])
                ->all();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function barbers(): array
    {
        return CatalogueCache::remember('barbers', 'all', function () {
            return Barber::query()
                ->with(['user'])
                ->where('is_available', true)
                ->orderBy('rating', 'desc')
                ->get()
                ->map(fn (Barber $barber) => $this->barberData($barber))
                ->all();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function barberById(int $id): ?array
    {
        return CatalogueCache::remember('barbers', "id:{$id}", function () use ($id) {
            $barber = Barber::query()->with(['user'])->find($id);

            return $barber ? $this->barberData($barber) : null;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function workingHours(): array
    {
        return CatalogueCache::remember('working_hours', 'all', function () {
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
        });
    }

    /**
     * @return array<int, string>
     */
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

    /**
     * @return array<string, mixed>
     */
    protected function barberData(Barber $barber): array
    {
        $avatar = app(UserMediaUrl::class)->for($barber->user, $barber->user?->avatar);

        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'avatar' => $avatar,
            'bio' => $barber->bio,
            'specialties' => $barber->specialties ?? [],
            'experience_years' => $barber->experience_years,
            'rating' => $barber->rating,
            'is_available' => $barber->is_available,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serviceData(Service $service): array
    {
        $imageUrl = null;
        if ($service->image) {
            if (str_starts_with($service->image, 'http')) {
                $imageUrl = $service->image;
            } else {
                $normalized = ltrim($service->image, '/');
                if (str_starts_with($normalized, 'storage/')) {
                    $normalized = substr($normalized, strlen('storage/'));
                }
                $imageUrl = url('storage/'.$normalized);
            }
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => $service->price,
            'duration_minutes' => $service->duration_minutes,
            'image' => $imageUrl ?? $service->image,
            'image_url' => $imageUrl,
            'is_available' => (bool) $service->is_active,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
            ] : null,
        ];
    }
}
