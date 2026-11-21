<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'transaction_type',
        'gateway',
        'gateway_event_id',
        'amount',
        'amount_minor',
        'idempotency_key',
        'raw_payload',
        'status',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'amount_minor' => 'integer',
        'raw_payload' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PaymentTransaction $tx) {
            if ($tx->amount_minor === null && $tx->amount !== null) {
                $tx->amount_minor = (int) $tx->amount;
            }
        });
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
