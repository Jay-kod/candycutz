<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\Barber;

class UpdateChairStatus
{
    public function execute(Barber $barber, string $status): Barber
    {
        $barber->update([
            'chair_status' => $status,
            'is_available' => $status !== 'offline',
        ]);

        return $barber->refresh();
    }
}
