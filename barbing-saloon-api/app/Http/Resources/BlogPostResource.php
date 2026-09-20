<?php

namespace App\Http\Resources;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BlogPost
 */
class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->featured_image) {
            $imageUrl = str_starts_with($this->featured_image, 'http')
                ? $this->featured_image
                : url('storage/'.ltrim($this->featured_image, '/'));
        }

        $isPublished = ($this->status instanceof \App\Domain\Shared\Enums\BlogStatus)
            ? $this->status === \App\Domain\Shared\Enums\BlogStatus::published
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
            'is_published' => $isPublished ? 1 : 0,
            'status' => ($this->status instanceof \App\Domain\Shared\Enums\BlogStatus) ? $this->status->value : ($this->status ?? 'draft'),
            'author' => [
                'id' => $this->author->id ?? null,
                'name' => $this->author_display ?? $this->author?->name ?? 'CandyCutz Team',
            ],
            'author_display' => $this->author_display ?? $this->author?->name ?? 'CandyCutz Team',
            'created_at' => $this->created_at?->toISOString(),
            'loves_count' => (int) ($this->relationLoaded('reactions') ? ($this->reactions->where('reaction_type', 'love')->count() ?? 0) : 0),
            'dislikes_count' => (int) ($this->relationLoaded('reactions') ? ($this->reactions->where('reaction_type', 'dislike')->count() ?? 0) : 0),
        ];

        return $data;
    }
}
