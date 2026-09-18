<?php

namespace App\Models;

use App\Domain\Shared\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_reference',
        'branch_id',
        'customer_id',
        'barber_id',
        'service_id',
        'appointment_type',
        'service_zone_id',
        'customer_address_id',
        'appointment_date',
        'appointment_time',
        'end_time',
        'total_duration_minutes',
        'total_amount',
        'travel_fee',
        'tip_amount',
        'discount_amount',
        'grand_total',
        'status',
        'cancellation_reason',
        'notes',
        'client_name',
        'client_phone',
        'client_email',
        'total_price',
        'deposit_paid',
        'deposit_amount',
    ];

    protected $casts = [
        'status' => AppointmentStatus::class,
        'appointment_date' => 'date',
        'deposit_paid' => 'boolean',
        'total_amount' => 'integer',
        'travel_fee' => 'integer',
        'tip_amount' => 'integer',
        'discount_amount' => 'integer',
        'grand_total' => 'integer',
        'total_price' => 'integer',
        'deposit_amount' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceZone(): BelongsTo
    {
        return $this->belongsTo(ServiceZone::class);
    }

    public function customerAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'customer_address_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AppointmentItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getVerificationCodeAttribute(): string
    {
        return $this->booking_reference ?: ('CC-'.str_pad($this->id, 5, '0', STR_PAD_LEFT));
    }
}
