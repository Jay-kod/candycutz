<?php

namespace App\Modules\Landing\Services;

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

    // ──────────────────────────────────────────
    // Services  (DB: is_available, NO slug, NO display_order)
    // ──────────────────────────────────────────
    public function services(): array
    {
        return Service::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => $this->serviceData($service))
            ->all();
    }

    public function serviceBySlug(string $slug): ?array
    {
        // Services table has no slug column — match by id instead
        $service = Service::query()->with('category')->find($slug);

        return $service ? $this->serviceData($service) : null;
    }

    public function serviceCategories(): array
    {
        // service_categories: id, name, description, icon — NO slug, NO display_order
        return ServiceCategory::query()
            ->withCount(['services as services_count' => fn ($query) => $query->where('is_available', true)])
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

    // ──────────────────────────────────────────
    // Barbers  (DB: experience_years, is_available, NO display_order, NO instagram_url, NO is_featured)
    // ──────────────────────────────────────────
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

    // ──────────────────────────────────────────
    // Gallery  (DB: title, barber_id, image_path, category (enum), description, is_featured, display_order)
    // ──────────────────────────────────────────
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

    // ──────────────────────────────────────────
    // Testimonials  (DB: customer_id, barber_id, rating, comment, is_approved — NO client_name, NO review, NO is_featured, NO service_id)
    // ──────────────────────────────────────────
    public function testimonials(): array
    {
        return Testimonial::query()
            ->with(['barber.user', 'customer'])
            ->where('is_approved', true)
            ->orderByDesc('rating')
            ->latest()
            ->get()
            ->map(fn (Testimonial $testimonial) => [
                'id' => $testimonial->id,
                'client_name' => $testimonial->customer?->name ?? 'Anonymous',
                'client_avatar' => $testimonial->customer?->avatar ?? null,
                'rating' => $testimonial->rating,
                'review' => $testimonial->comment,
                'barber' => $testimonial->barber ? $this->barberData($testimonial->barber) : null,
                'created_at' => $testimonial->created_at,
            ])
            ->all();
    }

    // ──────────────────────────────────────────
    // Blog  (DB: content, is_published — NO status, NO body, NO published_at)
    // ──────────────────────────────────────────
    public function blogPosts(): array
    {
        return BlogPost::query()
            ->with('author')
            ->where('is_published', true)
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

    // ──────────────────────────────────────────
    // Working Hours
    // ──────────────────────────────────────────
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

    // ──────────────────────────────────────────
    // Data transformers (single source of truth for JSON shape)
    // ──────────────────────────────────────────
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
            'is_available' => $service->is_available,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
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
            'content' => $post->content,
            'featured_image' => $post->featured_image,
            'is_published' => $post->is_published,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
        ];
    }
}