<?php

declare(strict_types=1);

namespace App\Domain\Media\DataObjects;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class GalleryData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?UploadedFile $image,
        public readonly ?int $barber_id,
        public readonly ?int $service_id,
        public readonly bool $is_featured,
        public readonly string $category
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->input('title', ''),
            description: $request->input('description'),
            image: $request->file('image'),
            barber_id: $request->input('barber_id') ? (int) $request->input('barber_id') : null,
            service_id: $request->input('service_id') ? (int) $request->input('service_id') : null,
            is_featured: $request->boolean('is_featured', false),
            category: $request->input('category', 'general')
        );
    }
}
