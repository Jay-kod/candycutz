<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Address */
class AddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $parts = array_filter([
            $this->street,
            $this->landmark ? "Near {$this->landmark}" : null,
            $this->city,
            $this->state,
        ]);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'street' => $this->street,
            'landmark' => $this->landmark,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'is_default' => (bool) $this->is_default,
            'formatted' => implode(', ', $parts),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
