<?php

namespace App\Models;

use App\Domain\Shared\Enums\BlogStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $body
 * @property string|null $featured_image
 * @property int $author_id
 * @property string $status
 * @property Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property User $author
 * @property Collection|BlogReaction[] $reactions
 */
class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'featured_image', 'author_id', 'status', 'published_at'];

    protected $casts = ['status' => BlogStatus::class, 'published_at' => 'datetime'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(BlogReaction::class, 'post_id');
    }
}
