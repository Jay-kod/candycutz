<?php

namespace App\Domain\Identity\Actions;

use App\Models\WorkingHour;

class CreateWorkingHour
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): WorkingHour
    {
        return WorkingHour::query()->create($data);
    }
}
