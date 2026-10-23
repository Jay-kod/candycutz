<?php

namespace App\Domain\Content\Actions;

use App\Models\FeedbackSubmission;
use Illuminate\Pagination\LengthAwarePaginator;

class GetFeedbackSubmissions
{
    /**
     * @param  array{status?: string, category?: string}  $filters
     * @return LengthAwarePaginator<FeedbackSubmission>
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<FeedbackSubmission> $paginator */
        $paginator = FeedbackSubmission::query()
            ->with('user:id,name,email')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->latest()
            ->paginate(25);

        return $paginator;
    }
}
