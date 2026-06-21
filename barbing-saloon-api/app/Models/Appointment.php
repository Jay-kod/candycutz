<?php

namespace App\Models;

use App\Core\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'client_name', 'client_phone', 'client_email', 'barber_id', 'service_id', 'appointment_date', 'appointment_time', 'status', 'notes', 'total_price', 'deposit_paid', 'deposit_amount'];

    protected $casts = [
        'status' => AppointmentStatus::class,
        'appointment_date' => 'date',
        'deposit_paid' => 'boolean',
        'total_price' => 'decimal:2',
    ];

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
}