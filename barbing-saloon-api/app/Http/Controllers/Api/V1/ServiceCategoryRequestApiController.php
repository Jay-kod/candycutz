<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalogue\Actions\ReviewServiceCategoryRequest as ReviewCategoryRequestAction;
use App\Domain\Catalogue\Exceptions\ServiceCategoryRequestConflict;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Services\CatalogueCache;
use App\Http\Requests\Api\V1\Admin\ReviewServiceCategoryRequest as ReviewServiceCategoryRequestInput;
use App\Http\Requests\Api\V1\Barber\StoreServiceCategoryProposalRequest;
use App\Http\Resources\ServiceCategoryRequestResource;
use App\Http\Responses\ApiResponse;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryRequestApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ServiceCategoryRequest::class);

        $query = ServiceCategoryRequest::query()
            ->with(['requester', 'reviewer', 'serviceCategory'])
            ->latest();

        if (($request->user()->role->value ?? $request->user()->role) === 'barber') {
            $query->where('requested_by', $request->user()->id);
        }

        if (in_array($request->query('status'), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->query('status'));
        }

        return ApiResponse::success(
            ServiceCategoryRequestResource::collection($query->limit(100)->get()),
            'Service category requests retrieved'
        );
    }

    public function store(StoreServiceCategoryProposalRequest $request): JsonResponse
    {
        $this->authorize('create', ServiceCategoryRequest::class);

        $validated = $request->validated();
        $slug = Str::slug($validated['name']);

        if ($slug === '') {
            return ApiResponse::error('Enter a category name containing letters or numbers.', [
                'name' => ['Enter a category name containing letters or numbers.'],
            ], 422, 'VALIDATION_FAILED');
        }

        $alreadyExists = ServiceCategory::query()->where('slug', $slug)->exists()
            || ServiceCategoryRequest::query()->where('slug', $slug)->where('status', 'pending')->exists();

        if ($alreadyExists) {
            return ApiResponse::error('This category already exists or is awaiting review.', [
                'name' => ['Choose a different category name.'],
            ], 422, 'VALIDATION_FAILED');
        }

        $categoryRequest = ServiceCategoryRequest::query()->create([
            'requested_by' => $request->user()->id,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'icon' => $validated['icon'] ?? null,
            'status' => 'pending',
        ]);

        NotificationDispatcher::categoryRequested($categoryRequest);

        return ApiResponse::success(
            new ServiceCategoryRequestResource($categoryRequest->load(['requester', 'reviewer', 'serviceCategory'])),
            'Category request submitted for admin review',
            201
        );
    }

    public function approve(
        int $id,
        Request $request,
        ReviewCategoryRequestAction $action
    ): JsonResponse {
        $categoryRequest = ServiceCategoryRequest::query()->findOrFail($id);
        $this->authorize('review', $categoryRequest);

        try {
            $reviewedRequest = $action->approve($categoryRequest, $request->user());
        } catch (ServiceCategoryRequestConflict $exception) {
            return ApiResponse::error($exception->getMessage(), [], 409, 'CATEGORY_REQUEST_CONFLICT');
        }

        CatalogueCache::bust('categories');

        NotificationDispatcher::categoryStatusChanged($reviewedRequest, 'approved');

        return ApiResponse::success(
            new ServiceCategoryRequestResource($reviewedRequest),
            'Service category request approved'
        );
    }

    public function reject(
        int $id,
        ReviewServiceCategoryRequestInput $request,
        ReviewCategoryRequestAction $action
    ): JsonResponse {
        $categoryRequest = ServiceCategoryRequest::query()->findOrFail($id);
        $this->authorize('review', $categoryRequest);

        try {
            $reviewedRequest = $action->reject(
                $categoryRequest,
                $request->user(),
                $request->validated('review_note')
            );
        } catch (ServiceCategoryRequestConflict $exception) {
            return ApiResponse::error($exception->getMessage(), [], 409, 'CATEGORY_REQUEST_CONFLICT');
        }

        NotificationDispatcher::categoryStatusChanged($reviewedRequest, 'rejected', $request->validated('review_note'));

        return ApiResponse::success(
            new ServiceCategoryRequestResource($reviewedRequest),
            'Service category request rejected'
        );
    }
}
