<?php

declare(strict_types=1);

namespace App\Domain\Identity\DataObjects;

use Illuminate\Http\Request;

class UserData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $phone,
        public readonly ?string $bio,
        public readonly ?string $specialties,
        public readonly ?string $instagram_url
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name', ''),
            phone: $request->input('phone'),
            bio: $request->input('bio'),
            specialties: $request->input('specialties'),
            instagram_url: $request->input('instagram_url')
        );
    }
}
