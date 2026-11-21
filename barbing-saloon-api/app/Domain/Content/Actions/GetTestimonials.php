<?php

namespace App\Domain\Content\Actions;

use App\Models\Testimonial;

class GetTestimonials
{
    /**
     * @return array<int, Testimonial>
     */
    public function execute(): array
    {
        return Testimonial::query()->with(['barber.user', 'customer'])->latest()->get()->all();
    }
}
