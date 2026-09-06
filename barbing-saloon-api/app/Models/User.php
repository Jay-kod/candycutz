<?php

namespace App\Models;

use App\Core\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'real_name',
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'auth_provider',
        'provider_id',
        'status',
        'is_active',
        'last_username_change_at',
        'deactivated_at',
        'notification_preferences',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'is_active' => 'boolean',
        'password' => 'hashed',
        'last_username_change_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'notification_preferences' => 'array',
    ];

    public function barber(): HasOne
    {
        return $this->hasOne(Barber::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'customer_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}