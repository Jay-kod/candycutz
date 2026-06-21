<?php

namespace App\Modules\Public\Services;

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
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PublicService
{
    public function settings(): array
    {
        return Setting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn (Collection $items) => $items->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])->all())
            ->all();
    }

    public function services(): array
    {
        return Service::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(fn (Service $service) => $this->serviceData($service))
            ->all();
    }

    public function serviceBySlug(string $slug): ?array
    {
        $service = Service::query()->with('category')->where('slug', $slug)->first();

        return $service ? $this->serviceData($service) : null;
    }

    public function serviceCategories(): array
    {
        return ServiceCategory::query()
            ->withCount(['services as services_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('display_order')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'display_order' => $category->display_order,
                'services_count' => $category->services_count,
            ])
            ->all();
    }

    public function barbers(): array
    {
        return Barber::query()
            ->with(['user'])
            ->orderBy('display_order')
            ->get()
            ->map(fn (Barber $barber) => $this->barberData($barber))
            ->all();
    }

    public function barberById(int $id): ?array
    {
        $barber = Barber::query()->with(['user'])->find($id);

        return $barber ? $this->barberData($barber) : null;
    }

    public function gallery(?string $category = null): array
    {
        return Gallery::query()
            ->with('barber.user')
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('display_order')
            ->get()
            ->map(fn (Gallery $gallery) => [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'description' => $gallery->description,
                'image_path' => $gallery->image_path,
                'category' => $gallery->category?->value ?? $gallery->category,
                'is_featured' => $gallery->is_featured,
                'display_order' => $gallery->display_order,
                'barber' => $gallery->barber ? $this->barberData($gallery->barber) : null,
            ])
            ->all();
    }

    public function testimonials(): array
    {
        return Testimonial::query()
            ->with(['service.category', 'barber.user'])
            ->where('is_approved', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->latest()
            ->get()
            ->map(fn (Testimonial $testimonial) => [
                'id' => $testimonial->id,
                'client_name' => $testimonial->client_name,
                'client_avatar' => $testimonial->client_avatar,
                'rating' => $testimonial->rating,
                'review' => $testimonial->review,
                'is_featured' => $testimonial->is_featured,
                'service' => $testimonial->service ? $this->serviceData($testimonial->service) : null,
                'barber' => $testimonial->barber ? $this->barberData($testimonial->barber) : null,
            ])
            ->all();
    }

    public function blogPosts(): array
    {
        return BlogPost::query()
            ->with('author')
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->latest()
            ->get()
            ->map(fn (BlogPost $post) => $this->blogData($post))
            ->all();
    }

    public function blogPostBySlug(string $slug): ?array
    {
        $post = BlogPost::query()->with('author')->where('slug', $slug)->first();

        return $post ? $this->blogData($post) : null;
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

        $slotHelper = new SlotHelper();

        return $slotHelper->generate($date, $barber, $service);
    }

    public function contact(array $data): array
    {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['message'],
        ];
    }

    protected function barberData(Barber $barber): array
    {
        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'bio' => $barber->bio,
            'specialties' => $barber->specialties ?? [],
            'years_experience' => $barber->years_experience,
            'instagram_url' => $barber->instagram_url,
            'display_order' => $barber->display_order,
            'is_featured' => $barber->is_featured,
        ];
    }

    protected function serviceData(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'price' => $service->price,
            'duration_minutes' => $service->duration_minutes,
            'image' => $service->image,
            'is_active' => $service->is_active,
            'display_order' => $service->display_order,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
                'slug' => $service->category->slug,
            ] : null,
        ];
    }

    protected function blogData(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'featured_image' => $post->featured_image,
            'published_at' => $post->published_at,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
        ];
    }
}