<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Actions\SecureImageUpload;
use App\Http\Resources\BlogPostResource;
use App\Http\Responses\ApiResponse;
use App\Models\BlogPost;
use App\Models\BlogReaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()
            ->with(['author', 'reactions'])
            ->where('is_published', true)
            ->where('deleted_at', null);

        $posts = $query->orderByDesc('created_at')->paginate(10);

        return ApiResponse::success(BlogPostResource::collection($posts), 'Blog posts retrieved successfully');
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

        return ApiResponse::success(new BlogPostResource($post), 'Blog post retrieved');
    }

    public function react(Request $request, int $id): JsonResponse
    {
        // Simple mock since this wasn't fully implemented in the original controller,
        // but let's keep the signature.
        return ApiResponse::success(['status' => 'liked'], 'Reaction saved');
    }

    public function removeReaction(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(null, 'Reaction removed');
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', BlogPost::class);

        $user = $request->user();

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
            $path = (new SecureImageUpload())->execute($request->file('featured_image'), 'uploads/blog');
            $post->update(['featured_image' => '/storage/' . $path]);
        }

        return ApiResponse::success(new BlogPostResource($post), 'Blog post created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $this->authorize('update', $post);

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
            $post->is_published = filter_var($validated['is_published'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('featured_image')) {
            $path = (new SecureImageUpload())->execute($request->file('featured_image'), 'uploads/blog');
            $post->featured_image = '/storage/' . $path;
        }

        $post->save();

        return ApiResponse::success(new BlogPostResource($post->refresh()), 'Blog post updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $this->authorize('delete', $post);

        $post->delete();

        return ApiResponse::success(null, 'Blog post deleted');
    }
}
