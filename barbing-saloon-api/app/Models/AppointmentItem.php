<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'service_id',
        'price',
        'duration_minutes',
        'created_at',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
