<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class FeatureTestimonial
{
    public function execute(Testimonial $testimonial, bool $isFeatured): Testimonial
    {
        $testimonial->update(['is_featured' => $isFeatured]);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
