<?php

namespace App\Domain\Identity\Actions;

use App\Models\WorkingHour;

class DeleteWorkingHour
{
    public function execute(WorkingHour $workingHour): void
    {
        $workingHour->delete();
    }
}
