<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\Service;

class GetServices
{
    public function execute(): array
    {
        return Service::query()->with('category')->orderBy('name')->get()->all();
    }
}
