<?php

namespace App\Domain\Content\Actions;

use App\Models\GalleryCategory;

class DeleteGalleryCategory
{
    public function execute(GalleryCategory $category): void
    {
        $category->delete();
    }
}
