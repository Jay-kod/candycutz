<?php

namespace App\Domain\Content\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\Gallery;

class DeleteGalleryItem
{
    use HasSecureUploads;

    public function execute(Gallery $gallery): void
    {
        if ($gallery->image_path) {
            $this->deleteFile($gallery->image_path);
        }

        $gallery->delete();
    }
}
