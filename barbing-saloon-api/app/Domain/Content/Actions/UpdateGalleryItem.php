<?php

namespace App\Domain\Content\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\Gallery;
use Illuminate\Http\UploadedFile;

class UpdateGalleryItem
{
    use HasSecureUploads;

    public function execute(Gallery $gallery, array $data): Gallery
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($gallery->image_path) {
                $this->deleteFile($gallery->image_path);
            }

            $data['image_path'] = $this->uploadFile($data['image'], 'gallery');
        }

        unset($data['image']);
        $gallery->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? $gallery->image_path,
            'category' => $data['category'],
            'barber_id' => $data['barber_id'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
        ]);

        return $gallery->refresh();
    }
}
