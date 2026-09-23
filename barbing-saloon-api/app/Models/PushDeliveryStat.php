<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushDeliveryStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_tokens',
        'valid_tokens',
        'expired_tokens',
        'sent_count',
        'delivered_count',
        'failed_count',
        'failure_reasons',
        'active_users_with_token',
    ];

    protected $casts = [
        'date' => 'date',
        'total_tokens' => 'integer',
        'valid_tokens' => 'integer',
        'expired_tokens' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
        'failure_reasons' => 'array',
        'active_users_with_token' => 'integer',
    ];
}
