<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        if (! $post) {
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
                : url('storage/'.ltrim($post->featured_image, '/'));
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

    public function react(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(['status' => 'liked'], 'Reaction saved');
    }

    public function removeReaction(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(null, 'Reaction removed');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Only barbers can create blog posts.', [], 403, 'FORBIDDEN_ROLE');
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        $post = BlogPost::create([
            'author_id' => $user->id,
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'slug' => Str::slug($validated['title']).'-'.time(),
            'is_published' => true,
        ]);

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'blog_'.$post->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), $filename);
            $post->update(['featured_image' => '/uploads/blog/'.$filename]);
        }

        return ApiResponse::success($this->formatPost($post), 'Blog post created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $post = BlogPost::where('author_id', $user->id)->where('id', $id)->first();

        if (! $post) {
            return ApiResponse::error("Blog post #{$id} not found or you don't have permission.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|image|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if (isset($validated['title'])) {
            $post->title = $validated['title'];
            $post->slug = Str::slug($validated['title']).'-'.time();
        }
        if (isset($validated['excerpt'])) {
            $post->excerpt = $validated['excerpt'];
        }
        if (isset($validated['content'])) {
            $post->content = $validated['content'];
        }
        if (isset($validated['is_published'])) {
            $post->is_published = $validated['is_published'];
        }

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'blog_'.$post->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), $filename);
            $post->featured_image = '/uploads/blog/'.$filename;
        }

        $post->save();

        return ApiResponse::success($this->formatPost($post, true), 'Blog post updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $post = BlogPost::where('author_id', $user->id)->where('id', $id)->first();

        if (! $post) {
            return ApiResponse::error("Blog post #{$id} not found or you don't have permission.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $post->delete();

        return ApiResponse::success(null, 'Blog post deleted');
    }
}
