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
        $today = today();
        $yesterday = today()->subDay();

        $totalAppointments = Appointment::query()->count();
        $completedAppointments = Appointment::query()->where('status', AppointmentStatus::completed->value)->count();
        $completionRate = $totalAppointments > 0 ? (int) round(($completedAppointments / $totalAppointments) * 100) : 0;

        $totalCustomers = User::query()->where('role', 'customer')->count();
        $newCustomersWeek = User::query()->where('role', 'customer')->where('created_at', '>=', now()->subDays(7))->count();

        // Revenue calculations
        $totalRevenue = (float) Appointment::query()
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');
        $todayRevenue = (float) Appointment::query()
            ->whereDate('appointment_date', $today)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');
        $monthRevenue = (float) Appointment::query()
            ->whereYear('appointment_date', $today->year)
            ->whereMonth('appointment_date', $today->month)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        // Revenue trend & Booking trend for last 7 days
        $revenueTrend = [];
        $bookingTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = today()->subDays($i);
            $dayStr = $d->format('D');
            $dateStr = $d->toDateString();

            $dayRev = (float) Appointment::query()
                ->whereDate('appointment_date', $dateStr)
                ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                ->sum('total_price');

            $dayCount = Appointment::query()
                ->whereDate('appointment_date', $dateStr)
                ->count();

            $revenueTrend[] = [
                'day' => $dayStr,
                'date' => $dateStr,
                'revenue' => $dayRev,
            ];
            $bookingTrend[] = [
                'day' => $dayStr,
                'date' => $dateStr,
                'count' => $dayCount,
            ];
        }

        // Status breakdown
        $statusBreakdown = [
            'completed' => Appointment::query()->where('status', AppointmentStatus::completed->value)->count(),
            'confirmed' => Appointment::query()->where('status', AppointmentStatus::confirmed->value)->count(),
            'pending' => Appointment::query()->where('status', AppointmentStatus::pending->value)->count(),
            'cancelled' => Appointment::query()->where('status', AppointmentStatus::cancelled->value)->count(),
            'no_show' => Appointment::query()->where('status', AppointmentStatus::no_show->value)->count(),
        ];

        // Top services
        $topServices = Service::query()
            ->with('category')
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                $revenue = (float) Appointment::query()
                    ->where('service_id', $s->id)
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'category' => $s->category?->name ?? 'General',
                    'bookings' => $s->appointments_count,
                    'revenue' => $revenue,
                ];
            })
            ->all();

        // Recent appointments
        $recentAppointments = Appointment::query()
            ->with(['service', 'customer', 'barber.user'])
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(6)
            ->get();

        // Recent activity from AuditLog
        $recentActivity = \App\Models\AuditLog::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'description' => $log->description ?? $log->action,
                    'created_at' => $log->created_at?->toIso8601String() ?? now()->toIso8601String(),
                ];
            })
            ->all();

        // Barber performance
        $barberPerformance = Barber::query()
            ->with('user')
            ->withCount(['appointments as total_appointments'])
            ->get()
            ->map(function ($b) {
                $completed = Appointment::query()
                    ->where('barber_id', $b->id)
                    ->where('status', AppointmentStatus::completed->value)
                    ->count();

                return [
                    'id' => $b->id,
                    'name' => $b->user?->name ?? 'Barber ' . $b->id,
                    'total_appointments' => $b->total_appointments,
                    'completed_appointments' => $completed,
                    'rating' => (float) ($b->rating ?? 5.0),
                ];
            })
            ->all();

        return [
            'stats' => [
                'appointments_today' => Appointment::query()->whereDate('appointment_date', $today)->count(),
                'appointments_yesterday' => Appointment::query()->whereDate('appointment_date', $yesterday)->count(),
                'pending_appointments' => Appointment::query()->where('status', AppointmentStatus::pending->value)->count(),
                'total_customers' => $totalCustomers,
                'new_customers_week' => $newCustomersWeek,
                'completion_rate' => $completionRate,
                'total_appointments' => $totalAppointments,
                'published_blog_posts' => BlogPost::query()->where('status', BlogStatus::published->value)->count(),
                'gallery_items' => Gallery::query()->count(),
                'services' => Service::query()->count(),
                'barbers' => Barber::query()->count(),
                'testimonials' => Testimonial::query()->count(),
            ],
            'revenue' => [
                'total_revenue' => $totalRevenue,
                'today_revenue' => $todayRevenue,
                'month_revenue' => $monthRevenue,
            ],
            'revenue_trend' => $revenueTrend,
            'booking_trend' => $bookingTrend,
            'status_breakdown' => $statusBreakdown,
            'top_services' => $topServices,
            'recent_appointments' => $recentAppointments,
            'recent_activity' => $recentActivity,
            'barber_performance' => $barberPerformance,
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

    public function updateTestimonial(Testimonial $testimonial, array $data): Testimonial
    {
        $testimonial->update(array_intersect_key($data, array_flip(['is_approved', 'rating', 'comment'])));

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }

    public function workingHours(): array
    {
        $barbers = Barber::with(['user', 'workingHours' => fn ($q) => $q->orderBy('day_of_week')])->get();

        return $barbers->map(function ($b) {
            return [
                'barber_id' => $b->id,
                'barber_name' => $b->user?->name ?? 'Barber #' . $b->id,
                'hours' => $b->workingHours->map(fn ($h) => [
                    'id' => $h->id,
                    'day_of_week' => $h->day_of_week,
                    'open_time' => substr($h->open_time, 0, 5),
                    'close_time' => substr($h->close_time, 0, 5),
                    'is_closed' => (bool) $h->is_closed,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    public function updateBarberWorkingHours(int $barberId, array $hours): array
    {
        $barber = Barber::findOrFail($barberId);
        foreach ($hours as $h) {
            if (!isset($h['day_of_week'])) {
                continue;
            }

            WorkingHour::updateOrCreate(
                ['barber_id' => $barber->id, 'day_of_week' => (int) $h['day_of_week']],
                [
                    'open_time' => $h['open_time'] ?? '09:00:00',
                    'close_time' => $h['close_time'] ?? '18:00:00',
                    'is_closed' => isset($h['is_closed']) ? (bool) $h['is_closed'] : false,
                ]
            );
        }

        return $this->workingHours();
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

    public function barbers(): array
    {
        return Barber::with(['user'])->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'user_id' => $b->user_id,
                'name' => $b->user?->name ?? 'Barber #' . $b->id,
                'email' => $b->user?->email ?? '',
                'phone' => $b->user?->phone ?? '',
                'avatar' => $b->user?->avatar,
                'status' => $b->status ?? ($b->user?->status ?? 'active'),
                'experience_years' => $b->years_experience ?? $b->experience_years ?? 5,
                'specialties' => $b->specialties ?? [],
                'bio' => $b->bio ?? '',
                'rating' => (float) ($b->rating ?: 5.0),
                'is_featured' => (bool) $b->is_featured,
                'is_available' => (bool) $b->is_available,
            ];
        })->all();
    }

    public function storeBarber(array $data): array
    {
        $password = !empty($data['password']) ? $data['password'] : 'BarberPass123!';
        $email = $data['email'];

        $existing = User::where('email', $email)->first();
        if ($existing) {
            abort(409, 'A user with this email already exists');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'phone' => $data['phone'] ?? null,
            'role' => 'barber',
            'status' => $data['status'] ?? 'active',
            'is_active' => true,
        ]);

        $specialties = $data['specialties'] ?? [];
        if (is_string($specialties)) {
            $specialties = array_filter(array_map('trim', explode(',', $specialties)));
        }

        $barber = Barber::create([
            'user_id' => $user->id,
            'bio' => $data['bio'] ?? null,
            'specialties' => $specialties,
            'years_experience' => $data['experience_years'] ?? 3,
            'status' => $data['status'] ?? 'active',
            'is_available' => true,
            'rating' => 5.0,
        ]);

        for ($d = 0; $d < 7; $d++) {
            WorkingHour::create([
                'barber_id' => $barber->id,
                'day_of_week' => $d,
                'open_time' => '08:00:00',
                'close_time' => '19:00:00',
                'is_closed' => false,
            ]);
        }

        return [
            'id' => $barber->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $barber->status,
        ];
    }

    public function updateBarber(int $id, array $data): array
    {
        $barber = Barber::with('user')->findOrFail($id);

        $userUpdates = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'status']));
        if (!empty($data['password'])) {
            $userUpdates['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        if (!empty($userUpdates) && $barber->user) {
            $barber->user->update($userUpdates);
        }

        $barberUpdates = [];
        if (isset($data['bio'])) $barberUpdates['bio'] = $data['bio'];
        if (isset($data['experience_years'])) $barberUpdates['years_experience'] = $data['experience_years'];
        if (isset($data['status'])) $barberUpdates['status'] = $data['status'];
        if (isset($data['specialties'])) {
            $barberUpdates['specialties'] = is_string($data['specialties'])
                ? array_filter(array_map('trim', explode(',', $data['specialties'])))
                : $data['specialties'];
        }

        if (!empty($barberUpdates)) {
            $barber->update($barberUpdates);
        }

        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'email' => $barber->user?->email,
            'status' => $barber->status,
        ];
    }

    public function updateBarberStatus(int $id, string $status): array
    {
        $barber = Barber::with('user')->findOrFail($id);
        $barber->update(['status' => $status]);
        if ($barber->user) {
            $barber->user->update(['status' => $status]);
        }

        return ['id' => $barber->id, 'status' => $status];
    }

    public function deleteBarber(int $id): void
    {
        $barber = Barber::with('user')->findOrFail($id);
        if ($barber->user) {
            $barber->user->delete();
        }
        $barber->delete();
    }

    public function customers(): array
    {
        return User::query()
            ->where('role', 'customer')
            ->withCount('appointments as total_bookings')
            ->get()
            ->map(function ($u) {
                $totalSpent = (float) Appointment::where('customer_id', $u->id)
                    ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
                    ->sum('total_price');

                $lastBooking = Appointment::where('customer_id', $u->id)
                    ->latest('appointment_date')
                    ->value('appointment_date');

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?? 'N/A',
                    'avatar' => $u->avatar,
                    'created_at' => $u->created_at?->toIso8601String(),
                    'total_bookings' => (int) $u->total_bookings,
                    'total_spent' => $totalSpent,
                    'last_booking_date' => $lastBooking,
                ];
            })
            ->all();
    }

    public function customerProfile(int $id): array
    {
        $customer = User::findOrFail($id);
        $appointments = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $customer->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'date' => $a->appointment_date,
                'time' => $a->appointment_time,
                'status' => $a->status,
                'service_name' => $a->service?->name ?? 'Custom Service',
                'barber_name' => $a->barber?->user?->name ?? 'Master Barber',
                'amount' => (float) $a->total_price,
            ]);

        $totalSpent = (float) Appointment::where('customer_id', $customer->id)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? 'N/A',
                'avatar' => $customer->avatar,
                'created_at' => $customer->created_at?->toIso8601String(),
            ],
            'total_bookings' => $appointments->count(),
            'total_spent' => $totalSpent,
            'appointments' => $appointments,
        ];
    }

    public function logs(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
            $logs = \Illuminate\Support\Facades\DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select(
                    'audit_logs.id',
                    'audit_logs.action',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(audit_logs.target_type, audit_logs.module, 'system') as entity_type"),
                    'audit_logs.ip_address',
                    'audit_logs.created_at',
                    'users.name as user_name'
                )
                ->latest('audit_logs.id')
                ->limit(100)
                ->get();

            if ($logs->isNotEmpty()) {
                return $logs->all();
            }
        }

        $recentAppts = Appointment::with(['customer', 'service', 'barber.user'])->latest()->limit(25)->get();

        return $recentAppts->map(fn ($a) => [
            'id' => $a->id,
            'user_name' => $a->customer?->name ?? 'System',
            'action' => 'booking_' . ($a->status instanceof \App\Core\Enums\AppointmentStatus ? $a->status->value : $a->status),
            'entity_type' => 'appointment',
            'ip_address' => '127.0.0.1',
            'created_at' => $a->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ])->all();
    }

    public function verifications(array $filters): array
    {
        $query = Appointment::query()->with(['customer', 'barber.user', 'service']);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($sub) use ($q) {
                $sub->where('booking_reference', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
            });
        }

        $filter = $filters['filter'] ?? 'all';
        if ($filter === 'confirmed' || $filter === 'pending') {
            $query->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value]);
        } elseif ($filter === 'completed') {
            $query->where('status', AppointmentStatus::completed->value);
        } elseif ($filter === 'expired') {
            $query->where('appointment_date', '<', today()->toDateString())->where('status', '!=', AppointmentStatus::completed->value);
        }

        return $query->latest('appointment_date')->latest('appointment_time')->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'verification_code' => $a->verification_code,
                'status' => $a->status instanceof \App\Core\Enums\AppointmentStatus ? $a->status->value : $a->status,
                'appointment_date' => $a->appointment_date?->toDateString() ?? $a->appointment_date,
                'appointment_time' => $a->appointment_time,
                'total_price' => (float) $a->total_price,
                'customer_name' => $a->customer?->name ?? $a->client_name ?? 'Client',
                'customer_phone' => $a->customer?->phone ?? $a->client_phone ?? 'N/A',
                'customer_email' => $a->customer?->email ?? $a->client_email ?? '',
                'barber_name' => $a->barber?->user?->name ?? 'Master Barber',
                'service_name' => $a->service?->name ?? 'Service',
            ];
        })->all();
    }

    public function verificationStats(): array
    {
        $total = Appointment::query()->count();
        $verifiedToday = Appointment::query()
            ->where('status', AppointmentStatus::completed->value)
            ->whereDate('updated_at', today())
            ->count();
        $pending = Appointment::query()
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->whereDate('appointment_date', '>=', today())
            ->count();
        $expired = Appointment::query()
            ->where('appointment_date', '<', today())
            ->where('status', '!=', AppointmentStatus::completed->value)
            ->count();

        return [
            'total' => $total,
            'verified_today' => $verifiedToday,
            'pending' => $pending,
            'expired' => $expired,
        ];
    }

    public function verifyAppointment(int $id): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => AppointmentStatus::completed->value,
            'deposit_paid' => true,
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            \Illuminate\Support\Facades\DB::table('payments')
                ->where('appointment_id', $appointment->id)
                ->update(['status' => 'successful', 'updated_at' => now()]);
        }

        return $appointment;
    }

    public function createWalkIn(array $data): Appointment
    {
        $service = Service::findOrFail($data['service_id']);
        $barber = Barber::findOrFail($data['barber_id']);

        $customerName = $data['customer_name'] ?? 'Walk-in Client';
        $customerPhone = $data['customer_phone'] ?? '';
        $customerEmail = $data['customer_email'] ?? 'walkin@candycutz.com';

        $user = User::where('phone', $customerPhone)->orWhere('email', $customerEmail)->first();
        if (!$user) {
            $user = User::create([
                'name' => $customerName,
                'email' => $customerEmail ?: 'walkin_' . time() . '@candycutz.com',
                'phone' => $customerPhone,
                'password' => \Illuminate\Support\Facades\Hash::make(uniqid()),
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        $apptDate = $data['appointment_date'] ?? today()->toDateString();
        $apptTime = $data['appointment_time'] ?? now()->format('H:i');

        return Appointment::create([
            'customer_id' => $user->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'client_name' => $customerName,
            'client_phone' => $customerPhone,
            'client_email' => $customerEmail,
            'appointment_date' => $apptDate,
            'appointment_time' => $apptTime,
            'status' => AppointmentStatus::confirmed->value,
            'total_price' => $service->price,
            'deposit_paid' => true,
            'verification_code' => 'CC-' . strtoupper(substr(uniqid(), -6)),
            'notes' => 'Walk-in booking',
        ]);
    }

    public function forceApproveAppointment(Appointment $appointment): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::confirmed->value,
            'deposit_paid' => true,
        ]);

        return $appointment->refresh();
    }
}