<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\ServiceCategory;

class CreateServiceCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): ServiceCategory
    {
        return ServiceCategory::query()->create($data);
    }
}
