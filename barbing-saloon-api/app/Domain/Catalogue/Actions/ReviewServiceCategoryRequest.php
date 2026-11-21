<?php

namespace App\Domain\Catalogue\Actions;

use App\Domain\Catalogue\Exceptions\ServiceCategoryRequestConflict;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewServiceCategoryRequest
{
    public function approve(ServiceCategoryRequest $categoryRequest, User $reviewer): ServiceCategoryRequest
    {
        return DB::transaction(function () use ($categoryRequest, $reviewer) {
            $lockedRequest = ServiceCategoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($categoryRequest->id);

            if ($lockedRequest->status !== 'pending') {
                throw new ServiceCategoryRequestConflict('This category request has already been reviewed.');
            }

            if (ServiceCategory::query()->where('slug', $lockedRequest->slug)->exists()) {
                throw new ServiceCategoryRequestConflict('A service category with this name already exists.');
            }

            $category = ServiceCategory::query()->create([
                'name' => $lockedRequest->name,
                'slug' => $lockedRequest->slug,
                'icon' => $lockedRequest->icon,
                'display_order' => ((int) ServiceCategory::query()->max('display_order')) + 1,
            ]);

            $lockedRequest->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'service_category_id' => $category->id,
                'reviewed_at' => now(),
            ]);

            return $lockedRequest->load(['requester', 'reviewer', 'serviceCategory']);
        });
    }

    public function reject(ServiceCategoryRequest $categoryRequest, User $reviewer, ?string $note): ServiceCategoryRequest
    {
        return DB::transaction(function () use ($categoryRequest, $reviewer, $note) {
            $lockedRequest = ServiceCategoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($categoryRequest->id);

            if ($lockedRequest->status !== 'pending') {
                throw new ServiceCategoryRequestConflict('This category request has already been reviewed.');
            }

            $lockedRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewer->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);

            return $lockedRequest->load(['requester', 'reviewer', 'serviceCategory']);
        });
    }
}
