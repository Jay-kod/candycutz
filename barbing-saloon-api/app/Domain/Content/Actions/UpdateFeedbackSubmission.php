<?php

namespace App\Domain\Content\Actions;

use App\Models\FeedbackSubmission;

class UpdateFeedbackSubmission
{
    /**
     * @param  array{status: string, admin_note?: ?string}  $data
     */
    public function execute(FeedbackSubmission $submission, array $data): FeedbackSubmission
    {
        $submission->update($data);

        return $submission->refresh()->load('user:id,name,email');
    }
}
