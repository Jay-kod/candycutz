<?php

namespace App\Domain\Identity\Actions;

use App\Models\Holiday;

class GetHolidays
{
    /**
     * @return array<int, Holiday>
     */
    public function execute(): array
    {
        return Holiday::query()->orderBy('date')->get()->all();
    }
}
