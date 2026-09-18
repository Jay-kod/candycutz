<?php

namespace App\Domain\Identity\Actions;

use App\Models\WorkingHour;

class CreateWorkingHour
{
    public function execute(array $data): WorkingHour
    {
        return WorkingHour::query()->create($data);
    }
}
