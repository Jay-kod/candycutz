<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $decided_at
 * @property Carbon|null $refunded_at
 */
class RefundRequest extends Model
{
    /** @use HasFactory<Factory<RefundRequest>> */
    use HasFactory;

    public const TYPES = ['standard', 'compassionate', 'no_show_dispute'];

    public const REASON_CATEGORIES = ['changed_mind', 'illness', 'emergency', 'barber_cancelled', 'other'];

    public const STATUSES = ['pending', 'approved', 'rejected', 'processing', 'completed', 'failed'];

    protected $fillable = [
        'appointment_id',
        'payment_id',
        'customer_id',
        'type',
        'reason_category',
        'reason_text',
        'supporting_evidence',
        'refund_amount_minor',
        'original_amount_minor',
        'refund_percentage',
        'status',
        'decided_by',
        'decided_at',
        'decision_note',
        'gateway_refund_ref',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount_minor' => 'integer',
            'original_amount_minor' => 'integer',
            'refund_percentage' => 'integer',
            'decided_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
