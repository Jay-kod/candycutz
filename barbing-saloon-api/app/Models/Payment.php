<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $appointment_id
 * @property int $customer_id
 * @property int $amount
 * @property string $currency
 * @property string $status
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'amount',
        'currency',
        'status',
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
    ];

    protected $casts = [
        'amount' => 'integer',
        'receipt_uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'sla_expires_at' => 'datetime',
    ];

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
