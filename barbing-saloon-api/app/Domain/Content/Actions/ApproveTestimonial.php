<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class ApproveTestimonial
{
    public function execute(Testimonial $testimonial, bool $isApproved): Testimonial
    {
        $testimonial->update([
            'is_approved' => $isApproved,
            'is_featured' => $isApproved && $testimonial->is_featured,
        ]);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
