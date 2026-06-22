<?php

namespace App\Modules\Barber\Services;

use App\Core\Enums\AppointmentStatus;
use App\Jobs\SendAdminNotification;
use App\Jobs\SendBookingThankYou;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\WorkingHour;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class BarberService
{
    use \App\Core\Traits\HasSecureUploads;
    public function barberForUser(User $user): Barber
    {
        return Barber::query()->with('user')->where('user_id', $user->id)->firstOrFail();
    }

    public function dashboard(User $user): array
    {
        $barber = $this->barberForUser($user);

        return [
            'profile' => $barber,
            'stats' => [
                'today_bookings' => Appointment::query()->where('barber_id', $barber->id)->whereDate('appointment_date', today())->count(),
                'upcoming_bookings' => Appointment::query()->where('barber_id', $barber->id)->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])->whereDate('appointment_date', '>=', today())->count(),
                'completed_bookings' => Appointment::query()->where('barber_id', $barber->id)->where('status', AppointmentStatus::completed->value)->count(),
                'no_show_count' => Appointment::query()->where('barber_id', $barber->id)->where('status', AppointmentStatus::no_show->value)->count(),
            ],
            'today_appointments' => Appointment::query()
                ->with(['service', 'customer'])
                ->where('barber_id', $barber->id)
                ->whereDate('appointment_date', today())
                ->orderBy('appointment_time')
                ->get(),
            'upcoming_appointments' => Appointment::query()
                ->with(['service', 'customer'])
                ->where('barber_id', $barber->id)
                ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
                ->whereDate('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(5)
                ->get(),
        ];
    }

    public function schedule(User $user): array
    {
        $barber = $this->barberForUser($user);

        return [
            'working_hours' => WorkingHour::query()->where('barber_id', $barber->id)->orderBy('day_of_week')->get(),
            'appointments' => Appointment::query()
                ->with(['service', 'customer'])
                ->where('barber_id', $barber->id)
                ->whereDate('appointment_date', '>=', today()->toDateString())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get(),
        ];
    }

    public function appointments(User $user): LengthAwarePaginator
    {
        $barber = $this->barberForUser($user);

        return Appointment::query()
            ->with(['service', 'customer'])
            ->where('barber_id', $barber->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->paginate(10);
    }

    public function complete(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::completed->value]);

        // Dispatch thank you email and admin notification
        SendBookingThankYou::dispatch($appointment);
        SendAdminNotification::dispatch($appointment, 'completed');

        return $appointment->refresh()->load(['service', 'customer']);
    }

    public function noShow(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => AppointmentStatus::no_show->value]);

        // Dispatch admin notification
        SendAdminNotification::dispatch($appointment, 'no_show');

        return $appointment->refresh()->load(['service', 'customer']);
    }

    public function profile(User $user): Barber
    {
        return $this->barberForUser($user)->load('user');
    }

    public function updateProfile(User $user, array $data): Barber
    {
        $barber = $this->barberForUser($user);

        $barber->update(array_intersect_key($data, array_flip(['bio', 'specialties', 'years_experience', 'instagram_url'])));
        $barber->user->update(array_intersect_key($data, array_flip(['name', 'phone'])));

        return $barber->refresh()->load('user');
    }

    public function services(User $user): array
    {
        return Service::query()->with('category')->get()->all();
    }

    public function storeService(User $user, array $data): Service
    {
        $barber = $this->barberForUser($user);
        $data['barber_id'] = $barber->id;
        
        $imagePath = isset($data['image']) && $data['image'] instanceof UploadedFile
            ? $this->uploadFile($data['image'], 'services')
            : null;

        return Service::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'] ?? 30,
            'category_id' => $data['category_id'],
            'image' => $imagePath,
            'barber_id' => $barber->id,
            // mapping frontend to DB schema where possible
            'is_available' => $data['is_active'] ?? true,
        ]);
    }

    public function updateService(User $user, Service $service, array $data): Service
    {
        $barber = $this->barberForUser($user);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($service->image) $this->deleteFile($service->image);
            $data['image'] = $this->uploadFile($data['image'], 'services');
        } else {
            unset($data['image']);
        }

        if (isset($data['is_active'])) {
            $data['is_available'] = $data['is_active'];
            unset($data['is_active']);
        }

        $service->update($data);
        return $service->refresh()->load('category');
    }

    public function deleteService(User $user, Service $service): void
    {
        $barber = $this->barberForUser($user);

        if ($service->image) $this->deleteFile($service->image);
        $service->delete();
    }

    public function gallery(User $user): array
    {
        return Gallery::query()->get()->all();
    }

    public function storeGallery(User $user, array $data, UploadedFile $image): Gallery
    {
        $barber = $this->barberForUser($user);
        $path = $this->uploadFile($image, 'gallery');

        return Gallery::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $path,
            'category' => $data['category'] ?? 'haircut',
            'barber_id' => $barber->id,
        ]);
    }

    public function deleteGallery(User $user, Gallery $gallery): void
    {
        $barber = $this->barberForUser($user);

        if ($gallery->image_path) $this->deleteFile($gallery->image_path);
        $gallery->delete();
    }

    public function blogPosts(User $user): array
    {
        return BlogPost::query()->with('author')->get()->all();
    }

    public function storeBlogPost(User $user, array $data): BlogPost
    {
        $imagePath = isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile
            ? $this->uploadFile($data['featured_image'], 'blog')
            : null;

        return BlogPost::query()->create([
            'title' => $data['title'],
            'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . time(),
            'excerpt' => $data['excerpt'] ?? '',
            'content' => $data['body'] ?? $data['content'] ?? '',
            'featured_image' => $imagePath,
            'author_id' => $user->id,
            'is_published' => isset($data['status']) ? ($data['status'] === 'published') : false,
        ]);
    }

    public function updateBlogPost(User $user, BlogPost $blogPost, array $data): BlogPost
    {
        $barber = $this->barberForUser($user);

        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            if ($blogPost->featured_image) $this->deleteFile($blogPost->featured_image);
            $data['featured_image'] = $this->uploadFile($data['featured_image'], 'blog');
        }

        if (isset($data['content'])) {
            // we already have content
        } elseif (isset($data['body'])) {
            $data['content'] = $data['body'];
            unset($data['body']);
        }

        if (isset($data['status'])) {
            $data['is_published'] = $data['status'] === 'published';
            unset($data['status']);
        }

        $blogPost->update($data);
        return $blogPost->refresh();
    }

    public function deleteBlogPost(User $user, BlogPost $blogPost): void
    {
        $barber = $this->barberForUser($user);

        if ($blogPost->featured_image) $this->deleteFile($blogPost->featured_image);
        $blogPost->delete();
    }
}