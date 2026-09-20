<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Content\Actions\ApproveTestimonial;
use App\Domain\Content\Actions\DeleteTestimonial;
use App\Domain\Content\Actions\FeatureTestimonial;
use App\Http\Resources\TestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Models\Testimonial;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::query()
            ->with(['customer', 'service', 'barber.user']);

        // Allow fetching unapproved if admin (optional, logic might be more complex)
        $user = $request->user('sanctum');
        if (! $user || ! in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'])) {
            $query->where('is_approved', true);
        }

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

        return ApiResponse::success(TestimonialResource::collection($testimonials), 'Testimonials retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required_without:comment|string|nullable',
            'comment' => 'nullable|string',
            'service_id' => 'nullable|integer',
            'barber_id' => 'nullable|integer',
        ]);

        $testimonial = Testimonial::create([
            'customer_id' => $user->id,
            'client_name' => $user->name,
            'client_avatar' => $user->avatar,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? $validated['comment'] ?? '',
            'service_id' => $validated['service_id'] ?? null,
            'barber_id' => $validated['barber_id'] ?? null,
            'is_approved' => false,
            'is_featured' => false,
        ]);

        return ApiResponse::success(new TestimonialResource($testimonial), 'Review submitted successfully. Awaiting approval.', 201);
    }

    public function customerReviews(Request $request): JsonResponse
    {
        $user = $request->user();
        $testimonials = Testimonial::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->latest()
            ->get();

        return ApiResponse::success(TestimonialResource::collection($testimonials), 'Customer reviews loaded');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('update', $testimonial);

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string',
        ]);

        $testimonial->update($validated);

        return ApiResponse::success(new TestimonialResource($testimonial->refresh()), 'Testimonial updated');
    }

    public function approve(int $id, ApproveTestimonial $action): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('update', $testimonial);

        return ApiResponse::success(new TestimonialResource($action->execute($testimonial)), 'Testimonial approved');
    }

    public function feature(int $id, FeatureTestimonial $action): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('update', $testimonial);

        return ApiResponse::success(new TestimonialResource($action->execute($testimonial)), 'Testimonial feature toggled');
    }

    public function destroy(int $id, DeleteTestimonial $action): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $this->authorize('delete', $testimonial);

        $action->execute($testimonial);

        return ApiResponse::success(null, 'Testimonial deleted');
    }
}
