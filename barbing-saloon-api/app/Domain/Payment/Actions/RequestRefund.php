<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Services\RefundPolicyEngine;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\Appointment;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RequestRefund
{
    public function __construct(
        private readonly RefundPolicyEngine $policyEngine,
        private readonly ProcessRefund $processRefund,
    ) {}

    public function execute(
        Appointment $appointment,
        User $actor,
        string $reasonCategory = 'changed_mind',
        ?string $reasonText = null,
        bool $isCompassionate = false,
        ?UploadedFile $evidence = null,
        string $typeOverride = '',
    ): ?RefundRequest {
        $isCompassionateRequest = $isCompassionate || in_array($reasonCategory, ['illness', 'emergency'], true);
        $policy = $this->policyEngine->getPolicy();

        if ($isCompassionateRequest && $policy[RefundPolicyEngine::KEY_REQUIRE_EVIDENCE] && $evidence === null) {
            throw ValidationException::withMessages([
                'evidence' => ['Supporting evidence is required for compassionate refund requests under current salon policy.'],
            ]);
        }

        $evidencePath = null;
        if ($evidence !== null) {
            $extension = strtolower($evidence->getClientOriginalExtension() ?: $evidence->guessExtension() ?: 'bin');
            $filename = Str::uuid().'.'.$extension;
            $stored = $evidence->storeAs('refund-evidence', $filename, 'local');
            $evidencePath = is_string($stored) ? $stored : null;
        }

        $requiresApproval = false;

        try {
            $refundRequest = DB::transaction(function () use (
                $appointment,
                $actor,
                $reasonCategory,
                $reasonText,
                $isCompassionateRequest,
                $evidencePath,
                $typeOverride,
                &$requiresApproval
            ): ?RefundRequest {
                /** @var Appointment $locked */
                $locked = Appointment::with('payment')->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

                $activeExists = RefundRequest::where('appointment_id', $locked->id)
                    ->whereIn('status', ['pending', 'approved', 'processing', 'completed'])
                    ->lockForUpdate()
                    ->exists();

                if ($activeExists) {
                    throw ValidationException::withMessages([
                        'appointment' => ['A refund request already exists for this appointment.'],
                    ]);
                }

                if ($locked->deposit_forfeited && $typeOverride !== 'no_show_dispute') {
                    throw ValidationException::withMessages([
                        'appointment' => ['The deposit for this appointment was marked forfeited. Submit a no-show appeal for admin review instead.'],
                    ]);
                }

                $eligibility = $this->policyEngine->evaluate($locked, $reasonCategory, $isCompassionateRequest);
                if (! $eligibility->eligible || $eligibility->refundAmountMinor <= 0) {
                    return null;
                }

                $type = $typeOverride !== '' ? $typeOverride : $eligibility->type;
                $requiresApproval = $eligibility->requiresApproval;

                $created = RefundRequest::create([
                    'appointment_id' => $locked->id,
                    'payment_id' => $locked->payment?->id,
                    'customer_id' => (int) ($locked->customer_id ?: $actor->id),
                    'type' => $type,
                    'reason_category' => $reasonCategory,
                    'reason_text' => $reasonText,
                    'supporting_evidence' => $evidencePath,
                    'refund_amount_minor' => $eligibility->refundAmountMinor,
                    'original_amount_minor' => $eligibility->originalAmountMinor,
                    'refund_percentage' => $eligibility->percentage,
                    'status' => $requiresApproval ? 'pending' : 'approved',
                ]);

                if ($requiresApproval) {
                    $locked->update([
                        'payment_status' => AppointmentPaymentStatus::refund_pending->value,
                    ]);
                }

                AuditLogger::log('refund.requested', 'refunds', $created, [], [
                    'type' => $type,
                    'reason_category' => $reasonCategory,
                    'refund_amount_minor' => $eligibility->refundAmountMinor,
                    'requires_approval' => $requiresApproval,
                ]);

                return $created;
            }, 3);
        } catch (\Throwable $e) {
            if ($evidencePath !== null) {
                Storage::disk('local')->delete($evidencePath);
            }

            throw $e;
        }

        if ($refundRequest === null) {
            if ($evidencePath !== null) {
                Storage::disk('local')->delete($evidencePath);
            }

            return null;
        }

        if ($evidencePath !== null && in_array(strtolower(pathinfo($evidencePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            app(SecureImageUpload::class)->dispatchOptimization(
                rawPath: $evidencePath,
                disk: 'local',
                modelClass: RefundRequest::class,
                modelKey: $refundRequest->id,
                attribute: 'supporting_evidence',
                prefixStorage: false,
                actorUserId: $refundRequest->customer_id,
                maxDim: 1600,
                quality: 90,
                generateThumbnail: false,
            );
        }

        if ($requiresApproval) {
            NotificationDispatcher::refundRequested($refundRequest);

            return $refundRequest->fresh(['appointment', 'payment', 'customer']);
        }

        return $this->processRefund->execute($refundRequest, $actor);
    }
}
