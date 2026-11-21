<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Services\CatalogueCache;
use App\Events\ReviewSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to review submissions — notifies the barber and admins
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

        NotificationDispatcher::reviewSubmitted($testimonial);
    }
}
