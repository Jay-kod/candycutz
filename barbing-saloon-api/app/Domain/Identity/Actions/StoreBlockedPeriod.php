<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use App\Models\BlockedPeriod;

class StoreBlockedPeriod
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Barber $barber, int $userId, array $data): BlockedPeriod
    {
        return BlockedPeriod::create([
            'barber_id' => $barber->id,
            'branch_id' => $barber->branch_id ?? 1,
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'reason' => $data['reason'] ?? 'Personal Time Off',
            'created_by' => $userId,
        ]);
    }
}
