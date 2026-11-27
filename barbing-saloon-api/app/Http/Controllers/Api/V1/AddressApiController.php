<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Account\StoreAddressRequest;
use App\Http\Resources\AddressResource;
use App\Http\Responses\ApiResponse;
use App\Models\Address;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return ApiResponse::success(AddressResource::collection($addresses), 'Saved addresses loaded.');
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $address = DB::transaction(function () use ($user, $data): Address {
            $hasExisting = Address::where('user_id', $user->id)->exists();
            $makeDefault = (bool) ($data['is_default'] ?? ! $hasExisting);

            if ($makeDefault) {
                Address::where('user_id', $user->id)->update(['is_default' => false]);
            }

            return Address::create([
                'user_id' => $user->id,
                'title' => $data['title'] ?? 'Home',
                'street' => $this->resolveStreet($data),
                'landmark' => $data['landmark'] ?? null,
                'city' => $data['city'] ?? 'Keffi',
                'state' => $data['state'] ?? 'Nasarawa',
                'postal_code' => $data['postal_code'] ?? '961101',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'is_default' => $makeDefault,
            ]);
        });

        return ApiResponse::success(new AddressResource($address), 'Address saved successfully.', 201);
    }

    public function update(StoreAddressRequest $request, int $id): JsonResponse
    {
        $address = Address::findOrFail($id);
        $this->authorize('update', $address);
        $data = $request->validated();

        DB::transaction(function () use ($address, $data): void {
            if (! empty($data['is_default'])) {
                Address::where('user_id', $address->user_id)->where('id', '!=', $address->id)->update(['is_default' => false]);
            }
            $address->update([
                'title' => $data['title'] ?? $address->title,
                'street' => $this->resolveStreet($data),
                'landmark' => $data['landmark'] ?? $address->landmark,
                'city' => $data['city'] ?? $address->city,
                'state' => $data['state'] ?? $address->state,
                'postal_code' => $data['postal_code'] ?? $address->postal_code,
                'latitude' => array_key_exists('latitude', $data) ? $data['latitude'] : $address->latitude,
                'longitude' => array_key_exists('longitude', $data) ? $data['longitude'] : $address->longitude,
                'is_default' => array_key_exists('is_default', $data) ? (bool) $data['is_default'] : $address->is_default,
            ]);
        });

        return ApiResponse::success(new AddressResource($address->fresh()), 'Address updated.');
    }

    public function setDefault(int $id): JsonResponse
    {
        $address = Address::findOrFail($id);
        $this->authorize('update', $address);

        DB::transaction(function () use ($address): void {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return ApiResponse::success(new AddressResource($address->fresh()), 'Default address updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $address = Address::findOrFail($id);
        $this->authorize('delete', $address);
        $address->delete();

        return ApiResponse::success([], 'Address removed.');
    }

    /**
     * Resolve a display street value, falling back to formatted coordinates for pinned locations.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveStreet(array $data): string
    {
        $street = trim((string) ($data['street'] ?? ''));
        if ($street !== '') {
            return $street;
        }

        return sprintf(
            'Pinned Location (%.5f, %.5f)',
            (float) $data['latitude'],
            (float) $data['longitude'],
        );
    }
}
