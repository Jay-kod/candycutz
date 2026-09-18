<?php

namespace App\Domain\Identity\Actions;

use App\Models\WorkingHour;

class UpdateWorkingHour
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(WorkingHour $workingHour, array $data): WorkingHour
    {
        $workingHour->update($data);

        return $workingHour->refresh();
    }
}
