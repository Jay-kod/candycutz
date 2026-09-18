<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Enums\ManualTransferState;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\AppointmentStatusHistory;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ManualTransferStateMachine
{
    /**
     * Resolve the current ManualTransferState from a Payment model.
     */
    public function getCurrentState(Payment $payment): ManualTransferState
    {
        return match ($payment->status) {
            'awaiting_transfer', 'pending' => ManualTransferState::awaiting_transfer,
            'receipt_uploaded' => ManualTransferState::receipt_uploaded,
            'under_review' => ManualTransferState::under_review,
            'successful', 'verified' => ManualTransferState::verified,
            'failed', 'rejected' => ManualTransferState::rejected,
            default => ManualTransferState::awaiting_transfer,
        };
    }

    /**
     * Transition a payment to a new state with validation, SLA tracking, and audit logging.
     *
     * @param  array<string, mixed>  $context
     */
    public function transition(Payment $payment, ManualTransferState $targetState, array $context = []): Payment
    {
        $currentState = $this->getCurrentState($payment);

        if (! $currentState->canTransitionTo($targetState)) {
            throw ValidationException::withMessages([
                'payment' => [
                    "Illegal state transition from '{$currentState->value}' to '{$targetState->value}'.",
                ],
            ]);
        }

        return DB::transaction(function () use ($payment, $currentState, $targetState, $context) {
            $updateData = [
                'status' => $targetState->value,
            ];

            $actorId = $context['actor_id'] ?? null;
            $reason = $context['reason'] ?? null;

            if ($targetState === ManualTransferState::receipt_uploaded) {
                $updateData['receipt_uploaded_at'] = now();
                $updateData['sla_expires_at'] = now()->addHours(24);
                if (isset($context['receipt_path'])) {
                    $updateData['receipt_url'] = $context['receipt_path'];
                }
            } elseif ($targetState === ManualTransferState::under_review) {
                if (empty($payment->sla_expires_at)) {
                    $updateData['sla_expires_at'] = now()->addHours(24);
                }
            } elseif ($targetState === ManualTransferState::verified) {
                // Map to 'successful' for standardized payment status queries
                $updateData['status'] = 'successful';
                $updateData['verified_at'] = now();
                if ($actorId) {
                    $updateData['verified_by_user_id'] = $actorId;
                }
            } elseif ($targetState === ManualTransferState::rejected) {
                // Map to 'failed' for standardized payment status queries
                $updateData['status'] = 'failed';
                $updateData['verified_at'] = now();
                $updateData['error_message'] = $reason ?? 'Receipt rejected';
                if ($actorId) {
                    $updateData['verified_by_user_id'] = $actorId;
                }
            }

            $payment->update($updateData);

            // Audit transaction record
            $transactionType = match ($targetState) {
                ManualTransferState::verified => 'capture',
                ManualTransferState::rejected => 'void',
                default => 'authorization',
            };

            PaymentTransaction::create([
                'payment_id' => $payment->id,
                'transaction_type' => $transactionType,
                'gateway' => 'manual_transfer',
                'gateway_event_id' => 'mt_transition_'.uniqid().'_'.$targetState->value,
                'amount' => $payment->amount,
                'raw_payload' => [
                    'previous_state' => $currentState->value,
                    'target_state' => $targetState->value,
                    'actor_id' => $actorId,
                    'reason' => $reason,
                    'sla_expires_at' => $payment->sla_expires_at?->toIso8601String(),
                ],
                'status' => $targetState->value,
                'created_at' => now(),
            ]);

            // If verified, confirm the associated appointment
            if ($targetState === ManualTransferState::verified) {
                $appointment = $payment->appointment;
                if ($appointment) {
                    $appointment->update([
                        'status' => AppointmentStatus::confirmed->value,
                        'deposit_paid' => true,
                    ]);

                    try {
                        AppointmentStatusHistory::create([
                            'appointment_id' => $appointment->id,
                            'previous_status' => AppointmentStatus::pending->value,
                            'new_status' => AppointmentStatus::confirmed->value,
                            'changed_by_user_id' => $actorId ?? $payment->customer_id,
                            'reason' => $reason ?? 'Manual transfer receipt verified',
                            'created_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Could not record status history for verified receipt: '.$e->getMessage());
                    }
                }
            }

            return $payment->refresh();
        });
    }
}
