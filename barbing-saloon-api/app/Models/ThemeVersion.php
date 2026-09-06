<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'theme_setting_id',
        'version_number',
        'tokens_json',
        'published_by_user_id',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'tokens_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function themeSetting(): BelongsTo
    {
        return $this->belongsTo(ThemeSetting::class);
    }

    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }
}
