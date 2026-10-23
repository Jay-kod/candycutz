<?php

namespace App\Models;

use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $appointment_id
 * @property int $customer_id
 * @property string $provider
 * @property string|null $reference
 * @property string|null $access_code
 * @property string|null $authorization_url
 * @property int $amount
 * @property int|null $amount_minor
 * @property string $currency
 * @property PaymentStatus $status
 * @property string|null $provider_transaction_id
 * @property string|null $channel
 * @property string|null $gateway_response
 * @property int $fees_minor
 * @property Carbon|null $paid_at
 * @property int $refunded_minor
 * @property string|null $failure_reason
 * @property string|null $client
 * @property string|null $customer_email
 * @property string|null $payment_method
 * @property string|null $gateway_reference
 * @property string|null $gateway_charge_id
 * @property string|null $transaction_ref
 * @property string|null $receipt_url
 * @property string|null $receipt_image
 * @property string|null $error_message
 * @property Carbon|null $receipt_uploaded_at
 * @property Carbon|null $verified_at
 * @property int|null $verified_by_user_id
 * @property Carbon|null $sla_expires_at
 * @property Carbon|null $expires_at
 * @property string|null $flagged_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'provider',
        'reference',
        'access_code',
        'authorization_url',
        'amount',
        'amount_minor',
        'currency',
        'status',
        'provider_transaction_id',
        'channel',
        'gateway_response',
        'fees_minor',
        'paid_at',
        'refunded_minor',
        'failure_reason',
        'client',
        'customer_email',
        'payment_method',
        'gateway_reference',
        'gateway_charge_id',
        'transaction_ref',
        'receipt_url',
        'error_message',
        'receipt_uploaded_at',
        'verified_at',
        'verified_by_user_id',
        'sla_expires_at',
        'expires_at',
        'flagged_reason',
    ];

    protected $casts = [
        'amount' => 'integer',
        'amount_minor' => 'integer',
        'fees_minor' => 'integer',
        'refunded_minor' => 'integer',
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'receipt_uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'sla_expires_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->transaction_ref) && ! empty($payment->reference)) {
                $payment->transaction_ref = $payment->reference;
            } elseif (empty($payment->reference) && ! empty($payment->transaction_ref)) {
                $payment->reference = $payment->transaction_ref;
            } elseif (empty($payment->transaction_ref) && empty($payment->reference)) {
                $ref = 'CC-'.strtoupper(Str::random(12));
                $payment->reference = $ref;
                $payment->transaction_ref = $ref;
            }
        });
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
