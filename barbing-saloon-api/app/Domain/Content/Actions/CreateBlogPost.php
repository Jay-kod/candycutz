<?php

namespace App\Domain\Content\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CreateBlogPost
{
    use HasSecureUploads;

    public function execute(User $author, array $data): BlogPost
    {
        $imagePath = isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile
            ? $this->uploadFile($data['featured_image'], 'blog')
            : null;

        return BlogPost::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']).'-'.time(),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['body'] ?? $data['content'] ?? '',
            'featured_image' => $imagePath,
            'author_id' => $author->id,
            'is_published' => isset($data['status']) ? ($data['status'] === 'published') : true,
        ]);
    }
}
