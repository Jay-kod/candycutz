<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    public $timestamps = true;

    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_id',
        'type',
        'category',
        'title',
        'message',
        'data',
        'action_url',
        'priority',
        'idempotency_key',
        'related_entity_id',
        'is_read',
        'push_sent_at',
        'push_delivered_at',
        'push_failed_at',
        'push_failure_reason',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'push_sent_at' => 'datetime',
        'push_delivered_at' => 'datetime',
        'push_failed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function isReadByUser(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        if ($this->recipient_id !== null) {
            return (int) $this->recipient_id === $userId ? (bool) $this->is_read : false;
        }

        if ($this->relationLoaded('reads')) {
            return $this->reads->contains('user_id', $userId);
        }

        return $this->reads()->where('user_id', $userId)->exists();
    }
}
