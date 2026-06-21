<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'client_name', 'client_avatar', 'rating', 'review', 'service_id', 'barber_id', 'is_approved', 'is_featured'];

    protected $casts = ['rating' => 'integer', 'is_approved' => 'boolean', 'is_featured' => 'boolean'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}