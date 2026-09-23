<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppCrash extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'error_message',
        'stack_trace',
        'component_stack',
        'app_version',
        'platform',
        'device_info',
        'resolved_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }
}
