<?php

namespace App\Domain\Content\Actions;

use App\Models\Gallery;

class GetGallery
{
    public function execute(): array
    {
        return Gallery::query()->with('barber.user')->orderBy('display_order')->get()->all();
    }
}
