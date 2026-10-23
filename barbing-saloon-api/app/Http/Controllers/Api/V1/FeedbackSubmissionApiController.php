<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Content\Actions\GetFeedbackSubmissions;
use App\Domain\Content\Actions\SubmitFeedbackSubmission;
use App\Domain\Content\Actions\UpdateFeedbackSubmission;
use App\Http\Requests\Api\V1\Admin\UpdateFeedbackSubmissionRequest;
use App\Http\Requests\Api\V1\StoreFeedbackSubmissionRequest;
use App\Http\Resources\FeedbackSubmissionResource;
use App\Http\Responses\ApiResponse;
use App\Models\FeedbackSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackSubmissionApiController
{
    public function store(StoreFeedbackSubmissionRequest $request, SubmitFeedbackSubmission $action): JsonResponse
    {
        $submission = $action->execute($request->user(), $request->validated());

        return ApiResponse::success(new FeedbackSubmissionResource($submission), 'Feedback submitted successfully', 201);
    }

    public function index(Request $request, GetFeedbackSubmissions $action): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['sometimes', 'string', 'in:new,reviewing,planned,completed,declined'],
            'category' => ['sometimes', 'string', 'in:feature_request,maintenance,improvement'],
        ]);
        $submissions = $action->execute($filters);
        $submissions->through(
            fn (FeedbackSubmission $submission) => (new FeedbackSubmissionResource($submission))->resolve($request)
        );

        return ApiResponse::paginated(
            $submissions,
            'Feedback submissions loaded'
        );
    }

    public function update(
        UpdateFeedbackSubmissionRequest $request,
        FeedbackSubmission $submission,
        UpdateFeedbackSubmission $action
    ): JsonResponse {
        $submission = $action->execute($submission, $request->validated());

        return ApiResponse::success(new FeedbackSubmissionResource($submission), 'Feedback submission updated');
    }
}
