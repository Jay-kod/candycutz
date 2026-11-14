<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $bio
 * @property string|null $bank_name
 * @property string|null $account_name
 * @property string|null $account_number
 * @property array<int, string>|null $specialties
 * @property int|null $experience_years
 * @property float $rating
 * @property bool $is_available
 * @property string $chair_status
 * @property-read User $user
 * @property-read Collection<int, WorkingHour> $workingHours
 * @property string $status
 */
class Barber extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'pending_approval', 'on_leave', 'suspended'];

    protected $fillable = [
        'user_id',
        'branch_id',
        'bio',
        'bank_name',
        'account_name',
        'account_number',
        'specialties',
        'years_experience',
        'experience_years',
        'rating',
        'is_available',
        'is_home_service_ready',
        'status',
        'chair_status',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Limit barbers to accounts that can be shown and selected for booking.
     *
     * @param  Builder<Barber>  $query
     * @return Builder<Barber>
     */
    public function scopeActiveForBooking(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where('is_available', true)
            ->whereHas('user', function (Builder $userQuery): void {
                $userQuery
                    ->where('is_active', true)
                    ->where(function (Builder $statusQuery): void {
                        $statusQuery->whereNull('status')->orWhere('status', 'active');
                    });
            });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
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

    /**
     * @return HasMany<WorkingHour, $this>
     */
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
