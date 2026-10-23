<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $provider
 * @property string $event
 * @property string $provider_event_key
 * @property bool $signature_valid
 * @property array $payload
 * @property string $status
 * @property int $attempts
 * @property string|null $error
 * @property string|null $request_id
 * @property Carbon $received_at
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'event',
        'provider_event_key',
        'signature_valid',
        'payload',
        'status',
        'attempts',
        'error',
        'request_id',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'payload' => 'array',
        'attempts' => 'integer',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function markProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
            'attempts' => $this->attempts + 1,
        ]);
    }
}
