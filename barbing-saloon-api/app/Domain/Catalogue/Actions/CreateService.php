<?php

namespace App\Domain\Catalogue\Actions;

use App\Domain\Shared\Traits\HasSecureUploads;
use App\Models\Service;
use Illuminate\Http\UploadedFile;

class CreateService
{
    use HasSecureUploads;

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Service
    {
        $imagePath = isset($data['image']) && $data['image'] instanceof UploadedFile
            ? $this->uploadFile($data['image'], 'services')
            : null;

        return Service::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'category_id' => $data['category_id'] ?? 1, // fallback
            'image' => $imagePath,
            'is_available' => $data['is_available'] ?? $data['is_active'] ?? true,
        ]);
    }
}
