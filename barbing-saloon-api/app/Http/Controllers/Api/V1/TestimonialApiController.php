<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::query()
            ->with(['customer', 'service', 'barber.user'])
            ->where('is_approved', true);

        if ($request->has('service_id') && is_numeric($request->service_id)) {
            $query->where('service_id', (int) $request->service_id);
        }

        if ($request->has('barber_id') && is_numeric($request->barber_id)) {
            $query->where('barber_id', (int) $request->barber_id);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $testimonials = $query->orderByDesc('created_at')->paginate(20);

        $data = $testimonials->map(fn (Testimonial $item) => $this->formatTestimonial($item));

        return ApiResponse::success($data, 'Testimonials retrieved successfully');
    }

    protected function formatTestimonial(Testimonial $item): array
    {
        $avatarUrl = null;
        if ($item->client_avatar) {
            $avatarUrl = str_starts_with($item->client_avatar, 'http')
                ? $item->client_avatar
                : url('storage/'.ltrim($item->client_avatar, '/'));
        } elseif ($item->customer?->avatar) {
            $avatarUrl = str_starts_with($item->customer->avatar, 'http')
                ? $item->customer->avatar
                : url('storage/'.ltrim($item->customer->avatar, '/'));
        }

        return [
            'id' => $item->id,
            'customer_name' => $item->client_name ?? $item->customer?->name ?? 'Anonymous',
            'avatar_url' => $avatarUrl,
            'rating' => (int) $item->rating,
            'comment' => $item->comment,
            'service' => $item->service ? [
                'id' => $item->service->id,
                'name' => $item->service->name,
            ] : null,
            'barber' => $item->barber ? [
                'id' => $item->barber->id,
                'name' => $item->barber->user?->name ?? 'Unknown',
            ] : null,
            'created_at' => $item->created_at?->toISOString(),
        ];
    }
}
