<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Shared\Services\CatalogueCache;
use App\Events\ReviewSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to review submissions — notifies the barber
 * about the new review.
 */
class HandleReviewSubmitted implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(ReviewSubmitted $event): void
    {
        $testimonial = $event->testimonial;

        Log::info("Review submitted: testimonial #{$testimonial->id}", [
            'testimonial_id' => $testimonial->id,
        ]);

        CatalogueCache::bust('testimonials');

        // Future: Notify the barber about their new review
        // Future: If rating is low, alert admin for follow-up
    }
}
