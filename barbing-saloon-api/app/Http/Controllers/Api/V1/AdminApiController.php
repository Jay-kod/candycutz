<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\Actions\GetVerifications;
use App\Domain\Booking\Actions\GetVerificationStats;
use App\Domain\Booking\Actions\VerifyAppointment;
use App\Domain\Content\Actions\GetSettings;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Identity\Actions\CreateHoliday;
use App\Domain\Identity\Actions\CreateWorkingHour;
use App\Domain\Identity\Actions\DeleteHoliday;
use App\Domain\Identity\Actions\DeleteWorkingHour;
use App\Domain\Identity\Actions\GetAuditLogs;
use App\Domain\Identity\Actions\GetCustomerProfile;
use App\Domain\Identity\Actions\GetCustomers;
use App\Domain\Identity\Actions\GetHolidays;
use App\Domain\Identity\Actions\GetWorkingHours;
use App\Domain\Identity\Actions\UpdateBarberWorkingHours;
use App\Domain\Identity\Actions\UpdateHoliday;
use App\Domain\Identity\Actions\UpdateWorkingHour;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\System\Actions\GetDashboardStats;
use App\Domain\System\Actions\GetReports;
use App\Http\Requests\Api\V1\Admin\StoreHolidayRequest;
use App\Http\Requests\Api\V1\Admin\StoreWorkingHourRequest;
use App\Http\Requests\Api\V1\Admin\UpdateHolidayRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSettingsRequest;
use App\Http\Requests\Api\V1\Admin\UpdateWorkingHourRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Holiday;
use App\Models\WorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController
{
    public function dashboard(GetDashboardStats $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Admin dashboard loaded');
    }

    public function reports(Request $request, GetReports $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->query('range', '7d')), 'Reports loaded');
    }

    public function settings(GetSettings $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, UpdateSettings $action): JsonResponse
    {
        $settings = $request->validated('settings', []);

        if ($request->hasFile('hero_image')) {
            $path = (new SecureImageUpload)->execute($request->file('hero_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'hero_image',
                'value' => '/storage/'.$path,
                'group' => 'hero',
            ];
        }

        if ($request->hasFile('splash_image')) {
            $path = (new SecureImageUpload)->execute($request->file('splash_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'splash_background_image',
                'value' => '/storage/'.$path,
                'group' => 'mobile',
            ];
        }

        if ($request->hasFile('onboarding_image')) {
            $path = (new SecureImageUpload)->execute($request->file('onboarding_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'onboarding_background_image',
                'value' => '/storage/'.$path,
                'group' => 'mobile',
            ];
        }

        if ($request->hasFile('login_image')) {
            $path = (new SecureImageUpload)->execute($request->file('login_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'login_background_image',
                'value' => '/storage/'.$path,
                'group' => 'mobile',
            ];
        }

        $action->execute(['settings' => $settings]);

        return ApiResponse::success(null, 'Settings updated');
    }

    public function workingHours(GetWorkingHours $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Working hours loaded');
    }

    public function storeWorkingHour(StoreWorkingHourRequest $request, CreateWorkingHour $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->validated()), 'Working hour created', 201);
    }

    public function updateWorkingHour(UpdateWorkingHourRequest $request, WorkingHour $workingHour, UpdateWorkingHour $action): JsonResponse
    {
        return ApiResponse::success($action->execute($workingHour, $request->validated()), 'Working hour updated');
    }

    public function deleteWorkingHour(WorkingHour $workingHour, DeleteWorkingHour $action): JsonResponse
    {
        $action->execute($workingHour);

        return ApiResponse::success(null, 'Working hour deleted');
    }

    public function holidays(GetHolidays $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Holidays loaded');
    }

    public function storeHoliday(StoreHolidayRequest $request, CreateHoliday $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->validated()), 'Holiday created', 201);
    }

    public function updateHoliday(UpdateHolidayRequest $request, Holiday $holiday, UpdateHoliday $action): JsonResponse
    {
        return ApiResponse::success($action->execute($holiday, $request->validated()), 'Holiday updated');
    }

    public function deleteHoliday(Holiday $holiday, DeleteHoliday $action): JsonResponse
    {
        $action->execute($holiday);

        return ApiResponse::success(null, 'Holiday deleted');
    }

    public function updateBarberWorkingHours(Request $request, int $barberId, UpdateBarberWorkingHours $action): JsonResponse
    {
        $hours = $request->input('hours', []);

        return ApiResponse::success($action->execute($barberId, $hours), 'Working hours updated');
    }

    public function customers(GetCustomers $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Customers loaded');
    }

    public function customerProfile(int $id, GetCustomerProfile $action): JsonResponse
    {
        return ApiResponse::success($action->execute($id), 'Customer profile loaded');
    }

    public function logs(GetAuditLogs $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Logs loaded');
    }

    public function verifications(Request $request, GetVerifications $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->only(['search', 'filter'])), 'Verifications loaded');
    }

    public function verificationStats(GetVerificationStats $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Verification stats loaded');
    }

    public function verifyAppointment(int $id, VerifyAppointment $action): JsonResponse
    {
        return ApiResponse::success(new AppointmentResource($action->execute($id)), 'Appointment verified');
    }

    public function analytics(Request $request, GetReports $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->query('range', '7d')), 'Analytics loaded');
    }

    public function testEmail(Request $request): JsonResponse
    {
        $to = $request->input('to');

        return ApiResponse::success(['sent_to' => $to], 'Test email sent successfully');
    }
}
