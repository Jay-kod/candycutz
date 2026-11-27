<?php

namespace App\Models;

use App\Domain\Shared\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $username
 * @property string|null $phone
 * @property string|null $avatar
 * @property string|null $cover_image
 * @property string|null $status
 * @property UserRole $role
 * @property int $no_show_count
 * @property Carbon|null $last_no_show_at
 * @property bool $requires_deposit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Barber|null $barber
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        });

        static::updated(function (User $user): void {
            if ($user->wasChanged('email')) {
                $user->sendEmailVerificationNotification();
            }
        });
    }

    protected $fillable = [
        'username',
        'real_name',
        'name',
        'email',
        'password',
        'avatar',
        'cover_image',
        'phone',
        'bio',
        'auth_provider',
        'provider_id',
        'last_username_change_at',
        'deactivated_at',
        'notification_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_id',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_username_change_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'notification_preferences' => 'array',
        'no_show_count' => 'integer',
        'last_no_show_at' => 'datetime',
        'requires_deposit' => 'boolean',
    ];

    /**
     * @return HasOne<Barber, $this>
     */
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

    /**
     * @return HasMany<UserConsent, $this>
     */
    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
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

    /**
     * @return HasMany<FeedbackSubmission, $this>
     */
    public function feedbackSubmissions(): HasMany
    {
        return $this->hasMany(FeedbackSubmission::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    public function notificationReads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    public function broadcastsCreated(): HasMany
    {
        return $this->hasMany(NotificationBroadcast::class, 'created_by');
    }

    public function channelPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /**
     * @return HasMany<NoShowRecord, $this>
     */
    public function noShowRecords(): HasMany
    {
        return $this->hasMany(NoShowRecord::class, 'customer_id');
    }

    /**
     * @return HasMany<RefundRequest, $this>
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'customer_id');
    }
}
