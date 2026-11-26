<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    /** @use HasFactory<Factory<UserConsent>> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_id',
        'type',
        'version',
        'accepted_at',
        'ip_address',
        'user_agent',
        'source',
        'metadata',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
