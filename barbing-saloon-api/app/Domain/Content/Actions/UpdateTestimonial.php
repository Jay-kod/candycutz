<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class UpdateTestimonial
{
    public function execute(Testimonial $testimonial, array $data): Testimonial
    {
        $testimonial->update(array_intersect_key($data, array_flip(['is_approved', 'rating', 'comment'])));

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
