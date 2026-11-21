<?php

namespace App\Domain\Content\Actions;

use App\Models\BlogPost;

class GetBlogPosts
{
    /**
     * @return array<int, BlogPost>
     */
    public function execute(): array
    {
        return BlogPost::query()->with('author')->latest()->get()->all();
    }
}
