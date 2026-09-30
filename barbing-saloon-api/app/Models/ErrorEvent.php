<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorEvent extends Model
{
    protected $fillable = [
        'group_id',
        'request_id',
        'occurred_at',
        'http_status',
        'method',
        'route_uri',
        'url_redacted',
        'user_id',
        'role',
        'client',
        'app_version',
        'device_label',
        'ip_hash',
        'user_agent',
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'context',
        'previous',
        'duration_ms',
        'memory_mb',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'http_status' => 'integer',
        'line' => 'integer',
        'duration_ms' => 'integer',
        'memory_mb' => 'float',
        'context' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ErrorGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
