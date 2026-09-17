<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use App\Models\BlogPost;
use App\Models\BlogReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()
            ->with(['author', 'reactions'])
            ->where('is_published', true)
            ->where('deleted_at', null);

        $posts = $query->orderByDesc('created_at')->paginate(10);

        $data = $posts->map(fn (BlogPost $post) => $this->formatPost($post));

        return ApiResponse::success($data, 'Blog posts retrieved successfully');
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->with(['author', 'reactions'])
            ->where('is_published', true)
            ->where('deleted_at', null)
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return ApiResponse::error("Blog post '{$slug}' not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success($this->formatPost($post, true), 'Blog post retrieved');
    }

    protected function formatPost(BlogPost $post, bool $detailed = false): array
    {
        $imageUrl = null;
        if ($post->featured_image) {
            $imageUrl = str_starts_with($post->featured_image, 'http')
                ? $post->featured_image
                : url('storage/' . ltrim($post->featured_image, '/'));
        }

        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?? '',
            'featured_image_url' => $imageUrl,
            'author' => [
                'id' => $post->author->id ?? null,
                'name' => $post->author_display ?? $post->author?->name ?? 'CandyCutz Team',
            ],
            'created_at' => $post->created_at?->toISOString(),
            'loves_count' => (int) ($post->reactions?->where('reaction_type', 'love')->count() ?? 0),
            'dislikes_count' => (int) ($post->reactions?->where('reaction_type', 'dislike')->count() ?? 0),
        ];

        if ($detailed) {
            $data['content'] = $post->content;
        }

        return $data;
    }
}