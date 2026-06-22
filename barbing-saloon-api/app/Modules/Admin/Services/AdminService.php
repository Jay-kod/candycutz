<?php

namespace App\Modules\Admin\Services;

use App\Core\Enums\AppointmentStatus;
use App\Core\Enums\BlogStatus;
use App\Core\Enums\GalleryCategory;
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
use App\Modules\Landing\Services\SlotHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use App\Models\User;

class AdminService
{
    use \App\Core\Traits\HasSecureUploads;

    public function dashboard(): array
    {
        return [
            'stats' => [
                'appointments_today' => Appointment::query()->whereDate('appointment_date', today())->count(),
                'pending_appointments' => Appointment::query()->where('status', AppointmentStatus::pending->value)->count(),
                'published_blog_posts' => BlogPost::query()->where('status', BlogStatus::published->value)->count(),
                'gallery_items' => Gallery::query()->count(),
                'services' => Service::query()->count(),
                'barbers' => Barber::query()->count(),
                'testimonials' => Testimonial::query()->count(),
            ],
            'reports' => $this->reports(),
        ];
    }

    public function settings(): array
    {
        return Setting::query()
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->all();
    }

    public function updateSettings(array $settings, ?UploadedFile $heroImage = null): array
    {
        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($heroImage) {
            $setting = Setting::query()->firstOrCreate(['key' => 'hero_image'], ['group' => 'hero']);
            if ($setting->value) {
                $this->deleteFile($setting->value);
            }
            
            $path = $this->uploadFile($heroImage, 'settings');
            $setting->update(['value' => $path]);
        }

        return $this->settings();
    }

    public function reports(): array
    {
        return [
            'appointments_by_status' => Appointment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'services_by_category' => ServiceCategory::query()->withCount('services')->orderBy('display_order')->get(),
            'featured_barbers' => Barber::query()->where('is_featured', true)->with('user')->orderBy('display_order')->get(),
            'revenue_estimate' => Appointment::query()->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])->sum('total_price'),
        ];
    }

    public function appointments(): LengthAwarePaginator
    {
        return Appointment::query()->with(['service', 'barber.user', 'customer'])->latest('appointment_date')->latest('appointment_time')->paginate(15);
    }

    public function approveAppointment(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::confirmed->value]);

        return $appointment->refresh()->load(['service', 'barber.user', 'customer']);
    }

    public function cancelAppointment(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::cancelled->value]);

        return $appointment->refresh()->load(['service', 'barber.user', 'customer']);
    }

    public function services(): array
    {
        return Service::query()->with('category')->orderBy('name')->get()->all();
    }

    public function storeService(array $data): Service
    {
        $imagePath = isset($data['image']) && $data['image'] instanceof UploadedFile
            ? $this->uploadFile($data['image'], 'services')
            : null;

        return Service::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'category_id' => $data['category_id'] ?? 1, // fallback
            'image' => $imagePath,
            'is_available' => $data['is_available'] ?? $data['is_active'] ?? true,
        ]);
    }

    public function updateService(Service $service, array $data): Service
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

    public function deleteService(Service $service): void
    {
        if ($service->image) {
            $this->deleteFile($service->image);
        }

        $service->delete();
    }

    public function serviceCategories(): array
    {
        return ServiceCategory::query()->orderBy('name')->get()->all();
    }

    public function storeServiceCategory(array $data): ServiceCategory
    {
        return ServiceCategory::query()->create($data);
    }

    public function updateServiceCategory(ServiceCategory $serviceCategory, array $data): ServiceCategory
    {
        $serviceCategory->update($data);

        return $serviceCategory->refresh();
    }

    public function deleteServiceCategory(ServiceCategory $serviceCategory): void
    {
        $serviceCategory->delete();
    }

    public function gallery(): array
    {
        return Gallery::query()->with('barber.user')->orderBy('display_order')->get()->all();
    }

    public function storeGallery(array $data, UploadedFile $image): Gallery
    {
        $path = $this->uploadFile($image, 'gallery');

        return Gallery::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $path,
            'category' => $data['category'],
            'barber_id' => $data['barber_id'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
        ]);
    }

    public function updateGallery(Gallery $gallery, array $data): Gallery
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

    public function deleteGallery(Gallery $gallery): void
    {
        if ($gallery->image_path) {
            $this->deleteFile($gallery->image_path);
        }

        $gallery->delete();
    }

    public function testimonials(): array
    {
        return Testimonial::query()->with(['barber.user', 'customer'])->latest()->get()->all();
    }

    public function approveTestimonial(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_approved' => true]);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }

    public function featureTestimonial(Testimonial $testimonial): Testimonial
    {
        // No is_featured in DB, just return the testimonial
        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }

    public function deleteTestimonial(Testimonial $testimonial): void
    {
        $testimonial->delete();
    }

    public function blogPosts(): array
    {
        return BlogPost::query()->with('author')->latest()->get()->all();
    }

    public function storeBlogPost(User $author, array $data): BlogPost
    {
        $imagePath = isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile
            ? $this->uploadFile($data['featured_image'], 'blog')
            : null;

        return BlogPost::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']) . '-' . time(),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['body'] ?? $data['content'] ?? '',
            'featured_image' => $imagePath,
            'author_id' => $author->id,
            'is_published' => isset($data['status']) ? ($data['status'] === 'published') : true,
        ]);
    }

    public function updateBlogPost(BlogPost $blogPost, array $data): BlogPost
    {
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            if ($blogPost->featured_image) {
                $this->deleteFile($blogPost->featured_image);
            }

            $data['featured_image'] = $this->uploadFile($data['featured_image'], 'blog');
        }

        if (isset($data['body'])) {
            $data['content'] = $data['body'];
            unset($data['body']);
        }
        if (isset($data['status'])) {
            $data['is_published'] = $data['status'] === 'published';
            unset($data['status']);
        }

        $blogPost->update($data);return $blogPost->refresh()->load('author');
    }

    public function deleteBlogPost(BlogPost $blogPost): void
    {
        if ($blogPost->featured_image) {
            $this->deleteFile($blogPost->featured_image);
        }

        $blogPost->delete();
    }

    public function workingHours(): array
    {
        return WorkingHour::query()->with('barber.user')->orderBy('day_of_week')->get()->all();
    }

    public function storeWorkingHour(array $data): WorkingHour
    {
        return WorkingHour::query()->create($data);
    }

    public function updateWorkingHour(WorkingHour $workingHour, array $data): WorkingHour
    {
        $workingHour->update($data);

        return $workingHour->refresh();
    }

    public function deleteWorkingHour(WorkingHour $workingHour): void
    {
        $workingHour->delete();
    }

    public function holidays(): array
    {
        return Holiday::query()->orderBy('date')->get()->all();
    }

    public function storeHoliday(array $data): Holiday
    {
        return Holiday::query()->create($data);
    }

    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->refresh();
    }

    public function deleteHoliday(Holiday $holiday): void
    {
        $holiday->delete();
    }
}