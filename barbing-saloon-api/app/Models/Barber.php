<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'bio',
        'specialties',
        'years_experience',
        'experience_years',
        'rating',
        'is_available',
        'is_home_service_ready',
        'status',
        'instagram_url',
        'display_order',
        'is_featured',
    ];

    protected $casts = [
        'specialties' => 'array',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'is_home_service_ready' => 'boolean',
        'rating' => 'float',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function barberServices(): HasMany
    {
        return $this->hasMany(BarberService::class);
    }

    public function blockedPeriods(): HasMany
    {
        return $this->hasMany(BlockedPeriod::class);
    }
}