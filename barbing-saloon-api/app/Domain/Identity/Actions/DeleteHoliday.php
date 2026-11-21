<?php

namespace App\Domain\Identity\Actions;

use App\Models\Holiday;

class DeleteHoliday
{
    public function execute(Holiday $holiday): void
    {
        $holiday->delete();
    }
}
