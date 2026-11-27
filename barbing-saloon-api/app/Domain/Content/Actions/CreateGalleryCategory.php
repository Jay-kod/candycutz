<?php

namespace App\Domain\Content\Actions;

use App\Models\GalleryCategory;

class CreateGalleryCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): GalleryCategory
    {
        return GalleryCategory::query()->create($data);
    }
}
