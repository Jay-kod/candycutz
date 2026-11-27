<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $appointment_id
 * @property int|null $customer_id
 * @property Carbon $marked_at
 * @property int|null $marked_by
 * @property string|null $reason
 * @property bool $was_automatic
 * @property bool $deposit_forfeited
 * @property int $deposit_forfeited_minor
 * @property Carbon|null $deposit_forfeited_at
 * @property int|null $deposit_forfeited_by
 * @property bool $grace_extended
 * @property string|null $grace_reason
 * @property bool $appealed
 * @property string|null $appeal_reason
 * @property Carbon|null $appealed_at
 * @property string|null $appeal_outcome
 * @property int|null $resolved_by
 * @property Carbon|null $resolved_at
 * @property string|null $resolution_note
 * @property-read Appointment $appointment
 * @property-read User|null $customer
 * @property-read User|null $markedBy
 * @property-read User|null $forfeitedBy
 * @property-read User|null $resolvedBy
 */
class NoShowRecord extends Model
{
    /** @use HasFactory<Factory<NoShowRecord>> */
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'marked_at',
        'marked_by',
        'reason',
        'was_automatic',
        'deposit_forfeited',
        'deposit_forfeited_minor',
        'deposit_forfeited_at',
        'deposit_forfeited_by',
        'grace_extended',
        'grace_reason',
        'appealed',
        'appeal_reason',
        'appealed_at',
        'appeal_outcome',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
        'was_automatic' => 'boolean',
        'deposit_forfeited' => 'boolean',
        'deposit_forfeited_minor' => 'integer',
        'deposit_forfeited_at' => 'datetime',
        'grace_extended' => 'boolean',
        'appealed' => 'boolean',
        'appealed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function forfeitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deposit_forfeited_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
