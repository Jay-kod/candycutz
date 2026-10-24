<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;
use Illuminate\Validation\ValidationException;

class FeatureTestimonial
{
    public function execute(Testimonial $testimonial, bool $isFeatured): Testimonial
    {
        if ($isFeatured && ! $testimonial->is_approved) {
            throw ValidationException::withMessages([
                'is_featured' => 'Approve the testimonial before displaying it on the website.',
            ]);
        }

        $testimonial->update(['is_featured' => $isFeatured]);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
