<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $barber_id
 * @property int $day_of_week
 * @property string $open_time
 * @property string $close_time
 * @property bool $is_closed
 * @property-read Barber|null $barber
 */
class WorkingHour extends Model
{
    use HasFactory;

    protected $fillable = ['barber_id', 'day_of_week', 'open_time', 'close_time', 'is_closed'];

    protected $casts = ['is_closed' => 'boolean'];

    /**
     * @return BelongsTo<Barber, $this>
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
