<?php

namespace App\Domain\Identity\Actions;

use App\Models\Holiday;

class CreateHoliday
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Holiday
    {
        return Holiday::query()->create($data);
    }
}
