<?php

namespace App\Domain\Content\Actions;

use App\Models\FeedbackSubmission;
use App\Models\User;

class SubmitFeedbackSubmission
{
    /**
     * @param  array{category: string, title: string, description: string}  $data
     */
    public function execute(User $user, array $data): FeedbackSubmission
    {
        return $user->feedbackSubmissions()->create($data)->refresh();
    }
}
