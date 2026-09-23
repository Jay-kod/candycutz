<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'category_id',
        'barber_id',
        'name',
        'slug',
        'description',
        'price',
        'duration_minutes',
        'image',
        'home_service_allowed',
        'is_available',
        'is_active',
        'approval_status',
        'approved_by',
        'approved_at',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration_minutes' => 'integer',
        'home_service_allowed' => 'boolean',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function appointmentItems(): HasMany
    {
        return $this->hasMany(AppointmentItem::class);
    }

    public function barberServices(): HasMany
    {
        return $this->hasMany(BarberService::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
}
