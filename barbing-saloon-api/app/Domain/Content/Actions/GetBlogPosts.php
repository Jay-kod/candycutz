<?php

namespace App\Domain\Content\Actions;

use App\Models\BlogPost;

class GetBlogPosts
{
    public function execute(): array
    {
        return BlogPost::query()->with('author')->latest()->get()->all();
    }
}
