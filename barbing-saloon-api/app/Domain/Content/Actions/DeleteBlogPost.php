<?php

namespace App\Domain\Content\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\BlogPost;

class DeleteBlogPost
{
    use HasSecureUploads;

    public function execute(BlogPost $blogPost): void
    {
        if ($blogPost->featured_image) {
            $this->deleteFile($blogPost->featured_image);
        }

        $blogPost->delete();
    }
}
