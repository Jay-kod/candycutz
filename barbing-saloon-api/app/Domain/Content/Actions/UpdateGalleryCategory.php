<?php

namespace App\Domain\Content\Actions;

use App\Models\Gallery;
use App\Models\GalleryCategory;
use Illuminate\Support\Facades\DB;

class UpdateGalleryCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(GalleryCategory $category, array $data): GalleryCategory
    {
        return DB::transaction(function () use ($category, $data): GalleryCategory {
            $oldSlug = $category->slug;
            $category->update($data);
            $newSlug = $category->slug;

            if ($oldSlug && $newSlug && $oldSlug !== $newSlug) {
                Gallery::query()
                    ->where('category', $oldSlug)
                    ->update(['category' => $newSlug]);
            }

            return $category->fresh();
        });
    }
}
