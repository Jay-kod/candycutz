<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberService extends Model
{
    use HasFactory;

    protected $fillable = [
        'barber_id',
        'service_id',
        'custom_price',
        'custom_duration',
        'is_offered',
    ];

    protected $casts = [
        'custom_price' => 'float',
        'custom_duration' => 'integer',
        'is_offered' => 'boolean',
    ];

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
