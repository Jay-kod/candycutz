<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\ManualTransferState;
use App\Domain\Payment\Services\ManualTransferStateMachine;
use App\Models\Payment;

class VerifyReceipt
{
    public function __construct(
        protected ManualTransferStateMachine $stateMachine
    ) {}

    public function execute(Payment $payment, bool $approve, ?string $reason = null, ?int $actorId = null): Payment
    {
        $targetState = $approve ? ManualTransferState::verified : ManualTransferState::rejected;

        return $this->stateMachine->transition(
            $payment,
            $targetState,
            [
                'reason' => $reason,
                'actor_id' => $actorId,
            ]
        );
    }
}
