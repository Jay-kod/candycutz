<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property string $client_name
 * @property string|null $client_avatar
 * @property int $rating
 * @property string|null $review
 * @property int|null $service_id
 * @property int|null $barber_id
 * @property bool $is_approved
 * @property bool $is_featured
 * @property-read User|null $customer
 * @property-read Service|null $service
 * @property-read Barber|null $barber
 */
class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'client_name', 'client_avatar', 'rating', 'review', 'service_id', 'barber_id', 'is_approved', 'is_featured'];

    protected $casts = ['rating' => 'integer', 'is_approved' => 'boolean', 'is_featured' => 'boolean'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<Barber, $this>
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
