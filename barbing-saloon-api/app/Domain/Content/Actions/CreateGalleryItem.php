<?php

namespace App\Domain\Content\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\Gallery;
use Illuminate\Http\UploadedFile;

class CreateGalleryItem
{
    use HasSecureUploads;

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, UploadedFile $image): Gallery
    {
        $path = $this->uploadFile($image, 'gallery');

        return Gallery::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $path,
            'category' => $data['category'],
            'barber_id' => $data['barber_id'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
        ]);
    }
}
