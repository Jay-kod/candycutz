<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class FeatureTestimonial
{
    public function execute(Testimonial $testimonial): Testimonial
    {
        $testimonial->update([
            'is_featured' => ! $testimonial->is_featured,
        ]);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
