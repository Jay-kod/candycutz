<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ApiGateLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiGateLog extends Model
{
    /** @use HasFactory<ApiGateLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'method',
        'path',
        'client_type',
        'status_code',
        'duration_ms',
        'query_count',
        'query_duration_ms',
        'memory_bytes',
        'user_id',
        'user_role',
        'ip_address',
        'is_slow',
        'budget_exceeded',
        'created_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'duration_ms' => 'integer',
        'query_count' => 'integer',
        'query_duration_ms' => 'integer',
        'memory_bytes' => 'integer',
        'is_slow' => 'boolean',
        'budget_exceeded' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
