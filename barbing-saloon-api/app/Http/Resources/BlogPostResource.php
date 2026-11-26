<?php

namespace App\Http\Resources;

use App\Domain\Shared\Enums\BlogStatus;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BlogPost
 */
class BlogPostResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        $thumbnailUrl = null;
        if ($this->featured_image) {
            if (str_starts_with($this->featured_image, 'http') || str_starts_with($this->featured_image, 'data:')) {
                $imageUrl = $this->featured_image;
                $thumbnailUrl = $this->featured_image;
            } else {
                $normalized = ltrim($this->featured_image, '/');
                if (str_starts_with($normalized, 'images/')) {
                    $imageUrl = url($normalized);
                    $thumbnailUrl = $imageUrl;
                } else {
                    $rel = str_starts_with($normalized, 'storage/') ? $normalized : 'storage/'.$normalized;
                    $imageUrl = url($rel);
                    if (str_ends_with(strtolower($rel), '.webp') && str_contains($rel, 'uploads/')) {
                        $base = basename($rel);
                        $thumbnailUrl = url(dirname($rel).'/'.(str_starts_with($base, 'thumb_') ? $base : 'thumb_'.$base));
                    } else {
                        $thumbnailUrl = $imageUrl;
                    }
                }
            }
        }

        $isPublished = ($this->status instanceof BlogStatus)
            ? $this->status === BlogStatus::published
            : $this->status === 'published';

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt ?? '',
            'content' => $this->body,
            'body' => $this->body,
            'featured_image_url' => $imageUrl,
            'featured_image' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl,
            'is_published' => $isPublished ? 1 : 0,
            'status' => ($this->status instanceof BlogStatus) ? $this->status->value : ($this->status ?? 'draft'),
            'author' => [
                'id' => $this->author->id ?? null,
                'name' => $this->author_display ?? $this->author->name ?? 'CandyCutz Team',
            ],
            'author_display' => $this->author_display ?? $this->author->name ?? 'CandyCutz Team',
            'created_at' => $this->created_at?->toISOString(),
            'loves_count' => (int) ($this->relationLoaded('reactions') ? ($this->reactions->where('reaction_type', 'love')->count() ?? 0) : 0),
            'dislikes_count' => (int) ($this->relationLoaded('reactions') ? ($this->reactions->where('reaction_type', 'dislike')->count() ?? 0) : 0),
        ];

        return $data;
    }
}
