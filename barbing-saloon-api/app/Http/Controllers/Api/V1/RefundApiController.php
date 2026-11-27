<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Payment\Actions\DecideRefundRequest;
use App\Domain\Payment\Actions\ProcessRefund;
use App\Domain\Payment\Actions\RequestRefund;
use App\Domain\Payment\Services\RefundPolicyEngine;
use App\Http\Requests\Payment\CompleteManualRefundRequest;
use App\Http\Requests\Payment\DecideRefundRequest as DecideRefundHttpRequest;
use App\Http\Requests\Payment\RequestRefundRequest;
use App\Http\Requests\Payment\UpdateRefundPolicyRequest;
use App\Http\Resources\RefundRequestResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\RefundRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RefundApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = RefundRequest::with(['appointment.service', 'appointment.barber.user', 'customer', 'payment', 'decider'])
            ->latest();

        if ($request->filled('status') && $request->string('status')->toString() !== 'all') {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('type') && $request->string('type')->toString() !== 'all') {
            $query->where('type', $request->string('type')->toString());
        }

        $refunds = $query->paginate(min(50, max(10, (int) $request->integer('per_page', 20))));

        return ApiResponse::success([
            'items' => RefundRequestResource::collection($refunds->items()),
            'meta' => [
                'current_page' => $refunds->currentPage(),
                'last_page' => $refunds->lastPage(),
                'total' => $refunds->total(),
            ],
            'stats' => [
                'pending' => RefundRequest::where('status', 'pending')->count(),
                'processing' => RefundRequest::where('status', 'processing')->count(),
                'completed' => RefundRequest::where('status', 'completed')->count(),
                'rejected' => RefundRequest::where('status', 'rejected')->count(),
                'total_refunded_minor' => (int) RefundRequest::where('status', 'completed')->sum('refund_amount_minor'),
            ],
        ]);
    }

    public function myRefunds(Request $request): JsonResponse
    {
        $refunds = RefundRequest::with(['appointment.service', 'appointment.barber.user', 'payment', 'decider'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return ApiResponse::success([
            'items' => RefundRequestResource::collection($refunds->items()),
            'meta' => [
                'current_page' => $refunds->currentPage(),
                'last_page' => $refunds->lastPage(),
                'total' => $refunds->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $refund = RefundRequest::with(['appointment.service', 'appointment.barber.user', 'customer', 'payment', 'decider'])->findOrFail($id);
        $this->authorize('view', $refund);

        return ApiResponse::success(new RefundRequestResource($refund));
    }

    public function eligibility(Request $request, int $id, RefundPolicyEngine $engine): JsonResponse
    {
        $appointment = Appointment::with('payment')->findOrFail($id);
        $this->authorize('view', $appointment);

        $reasonCategory = $request->string('reason_category', 'changed_mind')->toString();
        $isCompassionate = $request->boolean('compassionate', false);
        $eligibility = $engine->evaluate($appointment, $reasonCategory, $isCompassionate);

        return ApiResponse::success($eligibility->toArray());
    }

    public function store(RequestRefundRequest $request, RequestRefund $action): JsonResponse
    {
        $appointment = Appointment::with('payment')->findOrFail((int) $request->validated('appointment_id'));
        $this->authorize('cancel', $appointment);

        $refundRequest = $action->execute(
            appointment: $appointment,
            actor: $request->user(),
            reasonCategory: (string) $request->validated('reason_category'),
            reasonText: $request->validated('reason_text'),
            isCompassionate: (bool) $request->boolean('is_compassionate', false),
            evidence: $request->file('evidence'),
        );

        if (! $refundRequest) {
            throw ValidationException::withMessages([
                'appointment_id' => ['This appointment does not currently qualify for a refund request.'],
            ]);
        }

        return ApiResponse::success(
            new RefundRequestResource($refundRequest->load(['appointment.service', 'appointment.barber.user', 'customer', 'payment'])),
            'Refund request submitted successfully.',
            201
        );
    }

    public function decide(int $id, DecideRefundHttpRequest $request, DecideRefundRequest $action): JsonResponse
    {
        $refund = RefundRequest::findOrFail($id);
        $this->authorize('decide', $refund);

        $updated = $action->execute(
            refundRequest: $refund,
            admin: $request->user(),
            decision: (string) $request->validated('decision'),
            note: $request->validated('note'),
            overrideAmountMinor: $request->validated('refund_amount_minor') !== null ? (int) $request->validated('refund_amount_minor') : null,
        );

        return ApiResponse::success(new RefundRequestResource($updated), 'Refund decision recorded.');
    }

    public function completeManual(int $id, CompleteManualRefundRequest $request, ProcessRefund $action): JsonResponse
    {
        $refund = RefundRequest::findOrFail($id);
        $this->authorize('decide', $refund);

        $updated = $action->completeManual(
            refundRequest: $refund,
            actor: $request->user(),
            transferReference: $request->validated('transfer_reference'),
            note: $request->validated('note'),
        );

        return ApiResponse::success(new RefundRequestResource($updated), 'Manual refund marked as completed.');
    }

    public function policy(RefundPolicyEngine $engine): JsonResponse
    {
        return ApiResponse::success($engine->getPolicy());
    }

    public function updatePolicy(UpdateRefundPolicyRequest $request, UpdateSettings $updateSettings, RefundPolicyEngine $engine): JsonResponse
    {
        $validated = $request->validated();
        $payload = [];
        foreach ($validated as $key => $value) {
            $payload[] = [
                'key' => $key,
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'group' => 'booking',
            ];
        }
        $updateSettings->execute(['settings' => $payload]);

        return ApiResponse::success($engine->getPolicy(), 'Refund policy settings updated.');
    }

    public function evidence(int $id): StreamedResponse
    {
        $refund = RefundRequest::findOrFail($id);
        $this->authorize('view', $refund);

        $path = $refund->supporting_evidence;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Supporting evidence not found.');
        }

        $filename = basename($path);

        return Storage::disk('local')->response($path, $filename, [
            'Cache-Control' => 'private, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
