<?php

namespace App\Domain\Identity\Actions;

use App\Models\Holiday;

class UpdateHoliday
{
    public function execute(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->refresh();
    }
}
