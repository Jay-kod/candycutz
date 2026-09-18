<?php

namespace App\Domain\Catalogue\Actions;

use App\Models\ServiceCategory;

class DeleteServiceCategory
{
    public function execute(ServiceCategory $serviceCategory): void
    {
        $serviceCategory->delete();
    }
}
