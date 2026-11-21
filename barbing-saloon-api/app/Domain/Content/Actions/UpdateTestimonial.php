<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class UpdateTestimonial
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Testimonial $testimonial, array $data): Testimonial
    {
        $testimonial->update(array_intersect_key($data, array_flip(['is_approved', 'rating', 'review'])));

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
