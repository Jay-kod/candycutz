<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\ServiceCategory;

class GetServiceCategories
{
    /**
     * @return array<int, ServiceCategory>
     */
    public function execute(): array
    {
        return ServiceCategory::query()->orderBy('name')->get()->all();
    }
}
