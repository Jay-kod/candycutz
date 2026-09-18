<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class ApproveTestimonial
{
    public function execute(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_approved' => true]);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
