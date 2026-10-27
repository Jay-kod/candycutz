<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Domain\Shared\Services\CatalogueCache;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Testimonial;

class ContentService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function gallery(?string $category = null): array
    {
        $key = $category ? "cat:{$category}" : 'all';

        return CatalogueCache::remember('gallery', $key, function () use ($category): array {
            /** @var array<int, array<string, mixed>> $items */
            $items = Gallery::query()
                ->with('barber.user')
                ->when($category, fn ($query) => $query->where('category', $category))
                ->orderBy('display_order')
                ->get()
                ->map(function (Gallery $gallery): array {
                    $imageUrl = null;
                    if ($gallery->image_path) {
                        if (str_starts_with($gallery->image_path, 'http')) {
                            $imageUrl = $gallery->image_path;
                        } else {
                            $normalized = ltrim($gallery->image_path, '/');
                            if (str_starts_with($normalized, 'storage/')) {
                                $normalized = substr($normalized, strlen('storage/'));
                            }
                            $imageUrl = url('storage/'.$normalized);
                        }
                    }

                    return [
                        'id' => $gallery->id,
                        'title' => $gallery->title,
                        'description' => $gallery->description,
                        'image_path' => $gallery->image_path,
                        'image_url' => $imageUrl,
                        'category' => $gallery->category?->value ?? $gallery->category,
                        'barber_id' => $gallery->barber_id,
                        'barber_name' => $gallery->barber?->user?->name,
                        'is_featured' => $gallery->is_featured,
                        'display_order' => $gallery->display_order,
                        'barber' => $gallery->barber ? $this->barberData($gallery->barber) : null,
                    ];
                })
                ->all();

            return $items;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function testimonials(): array
    {
        return CatalogueCache::remember('testimonials', 'all', function () {
            return Testimonial::query()
                ->with(['barber.user', 'customer'])
                ->where('is_approved', true)
                ->orderByDesc('rating')
                ->latest()
                ->get()
                ->map(fn (Testimonial $testimonial): array => [
                    'id' => $testimonial->id,
                    'client_name' => $testimonial->customer?->name ?? $testimonial->client_name ?? 'Anonymous',
                    'client_avatar' => ($testimonial->client_avatar ? (str_starts_with($testimonial->client_avatar, 'http') ? $testimonial->client_avatar : url('storage/'.ltrim(str_replace('storage/', '', $testimonial->client_avatar), '/'))) : null) ?? app(UserMediaUrl::class)->for($testimonial->customer, $testimonial->customer?->avatar),
                    'rating' => $testimonial->rating,
                    'review' => $testimonial->review,
                    'barber' => $testimonial->barber ? $this->barberData($testimonial->barber) : null,
                    'created_at' => $testimonial->created_at,
                ])
                ->all();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function blogPosts(): array
    {
        return CatalogueCache::remember('blog', 'all', function () {
            return BlogPost::query()
                ->with('author')
                ->where('status', 'published')
                ->latest()
                ->get()
                ->map(fn (BlogPost $post): array => $this->blogData($post))
                ->all();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function blogPostBySlug(string $slug): ?array
    {
        return CatalogueCache::remember('blog', "slug:{$slug}", function () use ($slug) {
            $post = BlogPost::query()->with('author')->where('slug', $slug)->first();

            return $post ? $this->blogData($post) : null;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function contact(array $data): array
    {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['message'],
        ];
    }

    /**
     * @param  Barber  $barber
     * @return array<string, mixed>
     */
    protected function barberData(Barber $barber): array
    {
        $avatar = app(UserMediaUrl::class)->for($barber->user, $barber->user?->avatar);

        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'avatar' => $avatar,
            'bio' => $barber->bio,
            'specialties' => $barber->specialties ?? [],
            'experience_years' => $barber->experience_years,
            'rating' => $barber->rating,
            'is_available' => $barber->is_available,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function blogData(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->body,
            'featured_image' => $post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : url('storage/'.ltrim(str_replace('storage/', '', $post->featured_image), '/'))) : null,
            'status' => $post->status,
            'is_published' => $post->status === 'published',
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
        ];
    }
}
