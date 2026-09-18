<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DataObjects;

use Illuminate\Http\Request;

class ServiceData
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $duration_minutes,
        public readonly ?int $category_id,
        public readonly ?string $description,
        public readonly bool $is_active,
        public readonly ?string $image_url
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            price: (float) $request->input('price'),
            duration_minutes: (int) $request->input('duration_minutes', 30),
            category_id: $request->input('category_id') ? (int) $request->input('category_id') : null,
            description: $request->input('description'),
            is_active: $request->boolean('is_active', true),
            image_url: $request->input('image_url')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'image_url' => $this->image_url,
        ];
    }
}
