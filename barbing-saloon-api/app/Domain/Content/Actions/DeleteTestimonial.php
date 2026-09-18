<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class DeleteTestimonial
{
    public function execute(Testimonial $testimonial): void
    {
        $testimonial->delete();
    }
}
