<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\ServiceCategory;

class UpdateServiceCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(ServiceCategory $serviceCategory, array $data): ServiceCategory
    {
        $serviceCategory->update($data);

        return $serviceCategory->refresh();
    }
}
