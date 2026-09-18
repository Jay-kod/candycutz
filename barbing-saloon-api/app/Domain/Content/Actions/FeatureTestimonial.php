<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class FeatureTestimonial
{
    public function execute(Testimonial $testimonial): Testimonial
    {
        // No is_featured in DB, just return the testimonial
        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
