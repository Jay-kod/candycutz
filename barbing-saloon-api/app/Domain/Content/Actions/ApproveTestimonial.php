<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class ApproveTestimonial
{
    public function execute(Testimonial $testimonial, bool $isApproved): Testimonial
    {
        $changes = ['is_approved' => $isApproved];
        if (! $isApproved) {
            $changes['is_featured'] = false;
        }

        $testimonial->update($changes);

        return $testimonial->refresh()->load(['barber.user', 'customer']);
    }
}
