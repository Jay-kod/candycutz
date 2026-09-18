<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\Service;

class GetServices
{
    /**
     * @return array<int, Service>
     */
    public function execute(): array
    {
        return Service::query()->with('category')->orderBy('name')->get()->all();
    }
}
