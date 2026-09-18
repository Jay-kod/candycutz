<?php

namespace App\Domain\Identity\Actions;

use App\Models\Holiday;

class CreateHoliday
{
    public function execute(array $data): Holiday
    {
        return Holiday::query()->create($data);
    }
}
