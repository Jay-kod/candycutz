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

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt ?? '',
            'featured_image_url' => $imageUrl,
            'author' => [
                'id' => $this->author->id ?? null,
                'name' => $this->author_display ?? $this->author?->name ?? 'CandyCutz Team',
            ],
            'created_at' => $this->created_at?->toISOString(),
            'loves_count' => (int) ($this->reactions->where('reaction_type', 'love')->count() ?? 0),
            'dislikes_count' => (int) ($this->reactions->where('reaction_type', 'dislike')->count() ?? 0),
        ];

        // Only include content on detailed views (show/update)
        if ($request->routeIs('*.show') || $request->routeIs('*.update') || $request->routeIs('*.store')) {
            $data['content'] = $this->body;
        }

        return $data;
    }
}
