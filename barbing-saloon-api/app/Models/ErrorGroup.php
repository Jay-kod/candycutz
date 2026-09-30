<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErrorGroup extends Model
{
    protected $fillable = [
        'fingerprint',
        'error_code',
        'category',
        'severity',
        'exception_class',
        'sample_message',
        'source',
        'status',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'last_request_id',
        'affected_users_count',
        'assignee_id',
        'resolved_by',
        'resolved_at',
        'resolution_note',
        'regressed_at',
    ];

    protected $casts = [
        'occurrences' => 'integer',
        'affected_users_count' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'resolved_at' => 'datetime',
        'regressed_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(ErrorEvent::class, 'group_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
