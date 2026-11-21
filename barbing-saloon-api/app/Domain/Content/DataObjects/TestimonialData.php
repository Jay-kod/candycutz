<?php

declare(strict_types=1);

namespace App\Domain\Content\DataObjects;

use Illuminate\Http\Request;

class TestimonialData
{
    public function __construct(
        public readonly int $rating,
        public readonly string $content,
        public readonly ?int $barber_id,
        public readonly ?int $appointment_id,
        public readonly bool $is_published
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            rating: (int) $request->input('rating', 5),
            content: $request->input('content', ''),
            barber_id: $request->input('barber_id') ? (int) $request->input('barber_id') : null,
            appointment_id: $request->input('appointment_id') ? (int) $request->input('appointment_id') : null,
            is_published: $request->boolean('is_published', true)
        );
    }
}
