<?php

namespace App\Domain\Catalogue\Actions;

use App\Domain\Shared\Services\CatalogueCache;
use App\Models\ServiceCategory;

class GetServiceCategories
{
    /**
     * @return array<int, ServiceCategory>
     */
    public function execute(): array
    {
        return CatalogueCache::remember('categories', 'all_ordered', function () {
            return ServiceCategory::query()->orderBy('name')->get()->all();
        });
    }
}
