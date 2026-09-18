<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\ServiceCategory;

class CreateServiceCategory
{
    public function execute(array $data): ServiceCategory
    {
        return ServiceCategory::query()->create($data);
    }
}
