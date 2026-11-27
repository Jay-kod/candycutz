<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\Actions\AppealNoShow;
use App\Domain\Booking\Actions\ForfeitNoShowDeposit;
use App\Domain\Booking\Actions\GetNoShowDashboardData;
use App\Domain\Booking\Actions\InitiateNoShowRefund;
use App\Domain\Booking\Actions\RequestGracePeriod;
use App\Domain\Booking\Actions\ResolveNoShowAppeal;
use App\Domain\Booking\Services\NoShowPolicyService;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Shared\Services\AuditLogger;
use App\Http\Requests\Api\V1\AppealNoShowRequest;
use App\Http\Requests\Api\V1\ExtendGracePeriodRequest;
use App\Http\Requests\Api\V1\InitiateNoShowRefundRequest;
use App\Http\Requests\Api\V1\ResolveNoShowAppealRequest;
use App\Http\Requests\Api\V1\UpdateNoShowPolicyRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\NoShowRecordResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\NoShowRecord;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoShowApiController
{
    use AuthorizesRequests;

    public function index(Request $request, GetNoShowDashboardData $action): JsonResponse
    {
        $data = $action->execute(
            $request->query('filter'),
            $request->query('search'),
            (int) $request->query('per_page', 20)
        );

        return ApiResponse::success([
            'items' => NoShowRecordResource::collection($data['records']->items()),
            'pagination' => [
                'current_page' => $data['records']->currentPage(),
                'last_page' => $data['records']->lastPage(),
                'per_page' => $data['records']->perPage(),
                'total' => $data['records']->total(),
            ],
            'policy' => $data['policy'],
            'stats' => $data['stats'],
            'flagged_customers' => $data['flagged_customers'],
        ], 'No-show dashboard loaded.');
    }

    public function policy(NoShowPolicyService $service): JsonResponse
    {
        return ApiResponse::success($service->getConfig(), 'No-show policy settings retrieved.');
    }

    public function updatePolicy(UpdateNoShowPolicyRequest $request, UpdateSettings $updateSettings, NoShowPolicyService $service): JsonResponse
    {
        $validated = $request->validated();
        $settingsPayload = [];
        foreach ($validated as $key => $value) {
            $settingsPayload[] = [
                'key' => $key,
                'value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value,
                'group' => 'booking',
            ];
        }

        $updateSettings->execute(['settings' => $settingsPayload]);
        AuditLogger::log('no_show.policy_updated', 'Booking', $request->user(), [], $validated);

        return ApiResponse::success($service->getConfig(), 'No-show policy updated successfully.');
    }

    public function extendGrace(ExtendGracePeriodRequest $request, int $id, RequestGracePeriod $action): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('extendGrace', $appointment);

        $updated = $action->execute($appointment, $request->user(), $request->validated('reason'));

        return ApiResponse::success(new AppointmentResource($updated), 'Arrival grace period extended.');
    }

    public function forfeitDeposit(Request $request, int $id, ForfeitNoShowDeposit $action): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('markNoShow', $appointment);

        $record = $action->execute($appointment, $request->user(), $request->input('reason'));

        return ApiResponse::success(new NoShowRecordResource($record), 'Deposit marked as forfeited.');
    }

    public function forfeitRecordDeposit(Request $request, int $id, ForfeitNoShowDeposit $action): JsonResponse
    {
        $record = NoShowRecord::with('appointment')->findOrFail($id);
        $appointment = $record->appointment ?? Appointment::findOrFail($record->appointment_id);
        $this->authorize('markNoShow', $appointment);

        $updated = $action->execute($appointment, $request->user(), $request->input('reason'));

        return ApiResponse::success(new NoShowRecordResource($updated), 'Deposit marked as forfeited.');
    }

    public function appeal(AppealNoShowRequest $request, int $id, AppealNoShow $action): JsonResponse
    {
        $record = NoShowRecord::findOrFail($id);
        $updated = $action->execute($record, $request->user(), $request->validated('appeal_reason'));

        return ApiResponse::success(new NoShowRecordResource($updated), 'No-show appeal submitted for admin review.');
    }

    public function resolveAppeal(ResolveNoShowAppealRequest $request, int $id, ResolveNoShowAppeal $action, InitiateNoShowRefund $refundAction): JsonResponse
    {
        $record = NoShowRecord::findOrFail($id);
        $validated = $request->validated();
        $resolved = $action->execute($record, $request->user(), $validated['outcome'], $validated['resolution_note'] ?? null);

        if ($validated['outcome'] === 'approved' && ! empty($validated['initiate_refund'])) {
            $resolved = $refundAction->execute($resolved, $request->user(), null, $validated['resolution_note'] ?? 'Appeal approved refund');
        }

        return ApiResponse::success(new NoShowRecordResource($resolved), 'No-show appeal resolved.');
    }

    public function initiateRefund(InitiateNoShowRefundRequest $request, int $id, InitiateNoShowRefund $action): JsonResponse
    {
        $record = NoShowRecord::findOrFail($id);
        $validated = $request->validated();
        $updated = $action->execute($record, $request->user(), $validated['amount_minor'] ?? null, $validated['reason'] ?? null);

        return ApiResponse::success(new NoShowRecordResource($updated), 'Refund process initiated.');
    }

    public function resetCustomerFlag(Request $request, int $customerId): JsonResponse
    {
        $customer = User::findOrFail($customerId);
        $customer->forceFill(['requires_deposit' => false])->save();
        AuditLogger::log('customer.no_show_flag_reset', 'CustomerManagement', $request->user(), [], ['requires_deposit' => false], User::class, $customer->id);

        return ApiResponse::success(['id' => $customer->id, 'requires_deposit' => false], 'Customer deposit requirement cleared.');
    }

    public function customerHistory(Request $request, int $customerId): JsonResponse
    {
        $customer = User::findOrFail($customerId);
        $records = NoShowRecord::with(['appointment.service', 'appointment.barber.user', 'appointment.payment'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('marked_at')
            ->get();

        return ApiResponse::success([
            'no_show_count' => (int) ($customer->no_show_count ?? 0),
            'requires_deposit' => (bool) ($customer->requires_deposit ?? false),
            'records' => NoShowRecordResource::collection($records),
        ], 'Customer no-show history retrieved.');
    }
}
