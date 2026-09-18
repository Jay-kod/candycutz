<?php

namespace App\Domain\Content\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\BlogPost;
use Illuminate\Http\UploadedFile;

class UpdateBlogPost
{
    use HasSecureUploads;

    public function execute(BlogPost $blogPost, array $data): BlogPost
    {
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            if ($blogPost->featured_image) {
                $this->deleteFile($blogPost->featured_image);
            }

            $data['featured_image'] = $this->uploadFile($data['featured_image'], 'blog');
        }

        if (isset($data['body'])) {
            $data['content'] = $data['body'];
            unset($data['body']);
        }
        if (isset($data['status'])) {
            $data['is_published'] = $data['status'] === 'published';
            unset($data['status']);
        }

        $blogPost->update($data);

        return $blogPost->refresh()->load('author');
    }
}
