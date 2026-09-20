<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Actions\SecureImageUpload;
use App\Http\Resources\BlogPostResource;
use App\Http\Responses\ApiResponse;
use App\Models\BlogPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()->with(['author', 'reactions']);

        $user = $request->user();
        $userRole = $user?->role instanceof \App\Domain\Shared\Enums\UserRole
            ? $user->role->value
            : (string) ($user?->role ?? '');

        // If not staff/admin, only show published posts
        if (! in_array($userRole, ['admin', 'super_admin', 'barber'])) {
            $query->where('status', 'published');
        } elseif ($request->has('status') && $request->query('status') !== 'all') {
            $query->where('status', $request->query('status'));
        }

        $posts = $query->orderByDesc('created_at')->paginate(15);

        return ApiResponse::success(BlogPostResource::collection($posts), 'Blog posts retrieved successfully');
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $query = BlogPost::query()->with(['author', 'reactions']);
        if (is_numeric($slug)) {
            $query->where('id', (int) $slug);
        } else {
            $query->where('slug', $slug);
        }

        $post = $query->first();

        if (! $post) {
            return ApiResponse::error('Blog post not found.', [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success(new BlogPostResource($post), 'Blog post retrieved');
    }

    public function react(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Unauthorized', [], 401);
        }

        $type = $request->input('reaction_type') ?? $request->input('type') ?? 'love';
        \App\Models\BlogReaction::updateOrCreate(
            ['post_id' => $post->id, 'customer_id' => $user->id],
            ['reaction_type' => $type]
        );

        return ApiResponse::success(['status' => $type], 'Reaction saved');
    }

    public function removeReaction(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            \App\Models\BlogReaction::where('post_id', $id)->where('customer_id', $user->id)->delete();
        }

        return ApiResponse::success(null, 'Reaction removed');
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', BlogPost::class);

        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string',
            'excerpt' => 'nullable|string',
            'body' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|string',
            'is_published' => 'nullable',
        ]);

        $body = $validated['content'] ?? $validated['body'] ?? '';
        $status = 'draft';
        if (! empty($validated['status'])) {
            $status = $validated['status'];
        } elseif ($request->boolean('is_published') || $request->input('is_published') == '1') {
            $status = 'published';
        }

        $post = BlogPost::create([
            'author_id' => $user->id,
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($body), 150),
            'body' => $body,
            'slug' => Str::slug($validated['title']).'-'.time(),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        $imageFile = $request->file('featured_image') ?? $request->file('image');
        if ($imageFile) {
            $path = (new SecureImageUpload)->execute($imageFile, 'uploads/blog');
            $post->update(['featured_image' => '/storage/'.$path]);
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
            'body' => 'sometimes|string',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|image|max:2048',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|string',
            'is_published' => 'nullable',
        ]);

        if (isset($validated['title'])) {
            $post->title = $validated['title'];
            $post->slug = Str::slug($validated['title']).'-'.time();
        }
        if (isset($validated['excerpt'])) {
            $post->excerpt = $validated['excerpt'];
        }
        if (isset($validated['content'])) {
            $post->body = $validated['content'];
        } elseif (isset($validated['body'])) {
            $post->body = $validated['body'];
        }

        if (isset($validated['status'])) {
            $post->status = $validated['status'];
        } elseif ($request->has('is_published')) {
            $post->status = ($request->boolean('is_published') || $request->input('is_published') == '1')
                ? 'published'
                : 'draft';
        }

        $imageFile = $request->file('featured_image') ?? $request->file('image');
        if ($imageFile) {
            $path = (new SecureImageUpload)->execute($imageFile, 'uploads/blog');
            $post->featured_image = '/storage/'.$path;
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
