<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Testimonial;

class ContentService
{
    public function gallery(?string $category = null): array
    {
        return Gallery::query()
            ->with('barber.user')
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('display_order')
            ->get()
            ->map(fn (Gallery $gallery) => [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'description' => $gallery->description,
                'image_path' => $gallery->image_path,
                'category' => $gallery->category?->value ?? $gallery->category,
                'is_featured' => $gallery->is_featured,
                'display_order' => $gallery->display_order,
                'barber' => $gallery->barber ? $this->barberData($gallery->barber) : null,
            ])
            ->all();
    }

    public function testimonials(): array
    {
        return Testimonial::query()
            ->with(['barber.user', 'customer'])
            ->where('is_approved', true)
            ->orderByDesc('rating')
            ->latest()
            ->get()
            ->map(fn (Testimonial $testimonial) => [
                'id' => $testimonial->id,
                'client_name' => $testimonial->customer?->name ?? 'Anonymous',
                'client_avatar' => $testimonial->customer?->avatar ?? null,
                'rating' => $testimonial->rating,
                'review' => $testimonial->comment,
                'barber' => $testimonial->barber ? $this->barberData($testimonial->barber) : null,
                'created_at' => $testimonial->created_at,
            ])
            ->all();
    }

    public function blogPosts(): array
    {
        return BlogPost::query()
            ->with('author')
            ->where('is_published', true)
            ->latest()
            ->get()
            ->map(fn (BlogPost $post) => $this->blogData($post))
            ->all();
    }

    public function blogPostBySlug(string $slug): ?array
    {
        $post = BlogPost::query()->with('author')->where('slug', $slug)->first();
        return $post ? $this->blogData($post) : null;
    }

    public function contact(array $data): array
    {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['message'],
        ];
    }

    protected function barberData($barber): array
    {
        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'avatar' => $barber->user?->avatar,
            'bio' => $barber->bio,
            'specialties' => $barber->specialties ?? [],
            'experience_years' => $barber->experience_years,
            'rating' => $barber->rating,
            'is_available' => $barber->is_available,
        ];
    }

    protected function blogData(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'featured_image' => $post->featured_image,
            'is_published' => $post->is_published,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
        ];
    }
}