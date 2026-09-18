<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\ServiceCategory;

class UpdateServiceCategory
{
    public function execute(ServiceCategory $serviceCategory, array $data): ServiceCategory
    {
        $serviceCategory->update($data);

        return $serviceCategory->refresh();
    }
}
