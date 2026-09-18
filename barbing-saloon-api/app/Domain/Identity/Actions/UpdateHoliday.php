<?php

namespace App\Domain\Identity\Actions;

use App\Models\Holiday;

class UpdateHoliday
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->refresh();
    }
}
