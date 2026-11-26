<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\Actions\GetVerifications;
use App\Domain\Booking\Actions\GetVerificationStats;
use App\Domain\Booking\Actions\VerifyAppointment;
use App\Domain\Content\Actions\GetSettings;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Identity\Actions\CreateCustomer;
use App\Domain\Identity\Actions\CreateHoliday;
use App\Domain\Identity\Actions\CreateWorkingHour;
use App\Domain\Identity\Actions\DeleteCustomer;
use App\Domain\Identity\Actions\DeleteHoliday;
use App\Domain\Identity\Actions\DeleteWorkingHour;
use App\Domain\Identity\Actions\GetAuditLogs;
use App\Domain\Identity\Actions\GetCustomerProfile;
use App\Domain\Identity\Actions\GetCustomers;
use App\Domain\Identity\Actions\GetHolidays;
use App\Domain\Identity\Actions\GetWorkingHours;
use App\Domain\Identity\Actions\ResetCustomerPassword;
use App\Domain\Identity\Actions\UpdateBarberWorkingHours;
use App\Domain\Identity\Actions\UpdateCustomer;
use App\Domain\Identity\Actions\UpdateCustomerStatus;
use App\Domain\Identity\Actions\UpdateHoliday;
use App\Domain\Identity\Actions\UpdateWorkingHour;
use App\Domain\Notification\Services\ResendMailConfigurator;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\AuditLogger;
use App\Domain\System\Actions\GetDashboardStats;
use App\Domain\System\Actions\GetReportHistory;
use App\Domain\System\Actions\GetReports;
use App\Domain\System\Actions\LogReportExport;
use App\Http\Requests\Api\V1\Admin\StoreHolidayRequest;
use App\Http\Requests\Api\V1\Admin\StoreWorkingHourRequest;
use App\Http\Requests\Api\V1\Admin\UpdateHolidayRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSettingsRequest;
use App\Http\Requests\Api\V1\Admin\UpdateWorkingHourRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Holiday;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkingHour;
use App\Support\Errors\ErrorReporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

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

    public function reportHistory(Request $request, GetReportHistory $action): JsonResponse
    {
        return ApiResponse::success(
            $action->execute($request->query('action'), $request->query('search')),
            'Report export history loaded'
        );
    }

    public function logReportExport(Request $request, LogReportExport $action): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|max:50',
            'report_id' => 'required|string|max:100',
            'range' => 'nullable|string|max:20',
            'range_label' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:150',
            'metadata' => 'nullable|array',
            'checksum' => 'nullable|string|max:100',
        ]);

        return ApiResponse::success(
            $action->execute($request->user(), $validated, $request),
            'Report export event recorded',
            201
        );
    }

    public function settings(GetSettings $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, UpdateSettings $action, GetSettings $getSettings): JsonResponse
    {
        $settings = $request->validated('settings', []);
        if (empty($settings)) {
            $input = $request->except(['hero_image', 'page_hero_image', 'page_hero_page', 'about_teaser_image', 'about_shop_image', 'splash_image', 'onboarding_image', 'login_image', 'logo', 'favicon']);
            if (! empty($input)) {
                $settings = $input;
            }
        }

        if ($request->hasFile('hero_image')) {
            $path = (new SecureImageUpload)->execute($request->file('hero_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'hero_image',
                'value' => '/storage/'.$path,
                'group' => 'hero',
            ];
        }

        if ($request->hasFile('page_hero_image')) {
            $page = $request->validated('page_hero_page');
            $path = (new SecureImageUpload)->execute($request->file('page_hero_image'), 'uploads/settings');
            $settings[] = [
                'key' => $page === 'home' ? 'hero_image' : "page_hero_{$page}_image",
                'value' => '/storage/'.$path,
                'group' => 'hero',
            ];
        }

        if ($request->hasFile('about_teaser_image')) {
            $path = (new SecureImageUpload)->execute($request->file('about_teaser_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'about_teaser_image',
                'value' => '/storage/'.$path,
                'group' => 'about',
            ];
        }

        if ($request->hasFile('about_shop_image')) {
            $path = (new SecureImageUpload)->execute($request->file('about_shop_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'about_shop_image',
                'value' => '/storage/'.$path,
                'group' => 'about',
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

        if ($request->hasFile('logo')) {
            $path = (new SecureImageUpload)->execute($request->file('logo'), 'uploads/settings');
            $settings[] = [
                'key' => 'logo',
                'value' => '/storage/'.$path,
                'group' => 'general',
            ];
        }

        if ($request->hasFile('favicon')) {
            $path = (new SecureImageUpload)->execute($request->file('favicon'), 'uploads/settings');
            $settings[] = [
                'key' => 'favicon',
                'value' => '/storage/'.$path,
                'group' => 'general',
            ];
        }

        $action->execute(['settings' => $settings]);

        if (is_array($settings)) {
            $googleSettingKeys = array_values(array_filter(
                array_keys($settings),
                fn ($key): bool => is_string($key) && str_starts_with($key, 'google_')
            ));
            if ($googleSettingKeys !== []) {
                AuditLogger::log(
                    'google_auth_settings_updated',
                    'GoogleAuthentication',
                    null,
                    [],
                    ['keys' => $googleSettingKeys]
                );
            }
        }

        return ApiResponse::success($getSettings->execute(), 'Settings updated');
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

    public function storeCustomer(Request $request, CreateCustomer $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        return ApiResponse::success($action->execute($validated), 'Customer created successfully', 201);
    }

    public function customerProfile(int $id, GetCustomerProfile $action): JsonResponse
    {
        return ApiResponse::success($action->execute($id), 'Customer profile loaded');
    }

    public function updateCustomer(Request $request, int $id, UpdateCustomer $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        return ApiResponse::success($action->execute($id, $validated), 'Customer updated successfully');
    }

    public function updateCustomerStatus(Request $request, int $id, UpdateCustomerStatus $action): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,suspended,deactivated'],
        ]);

        $result = $action->execute($id, $validated);
        AuditLogger::log('customer_status_updated', 'CustomerManagement', null, [], $validated, 'User', $id);

        return ApiResponse::success($result, 'Customer status updated successfully');
    }

    public function resetCustomerPassword(Request $request, int $id, ResetCustomerPassword $action): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $result = $action->execute($id, $validated['password']);
        AuditLogger::log('customer_password_reset', 'CustomerManagement', null, [], [], 'User', $id);

        return ApiResponse::success($result, 'Customer password reset successfully');
    }

    public function deleteCustomer(int $id, DeleteCustomer $action): JsonResponse
    {
        $action->execute($id);
        AuditLogger::log('customer_deleted', 'CustomerManagement', null, [], [], 'User', $id);

        return ApiResponse::success(null, 'Customer deleted successfully');
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

    public function verifyAppointment(int $id, VerifyAppointment $action, Request $request): JsonResponse
    {
        return ApiResponse::success(new AppointmentResource($action->execute($id, $request->user())), 'Appointment verified');
    }

    public function analytics(Request $request, GetReports $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->query('range', '7d')), 'Analytics loaded');
    }

    public function testEmail(Request $request): JsonResponse
    {
        $to = (string) $request->validate(['to' => ['required', 'email']])['to'];

        // Guard against rapid duplicate test dispatches (respect Resend rate limits)
        $throttleKey = 'admin-test-email:'.($request->user()?->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return ApiResponse::error(
                "Email test rate limit reached. Please wait {$seconds} seconds before testing again.",
                ['retry_after' => $seconds],
                429,
                'TOO_MANY_REQUESTS'
            );
        }
        RateLimiter::hit($throttleKey, 60);

        $admin = $request->user();
        $notification = null;
        if ($admin) {
            $notification = Notification::create([
                'recipient_id' => $admin->id,
                'recipient_type' => 'user',
                'type' => 'resend_connection_test',
                'category' => 'account',
                'title' => 'Resend Connection Test',
                'message' => "Test email dispatched to {$to}",
                'data' => ['test_recipient' => $to],
                'is_read' => true,
            ]);
        }

        $delivery = null;
        if ($notification) {
            $delivery = NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => 'email',
                'recipient_email' => $to,
                'status' => 'pending',
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]);
        }

        // Dynamically ensure Resend credentials from settings take effect for this dispatch
        ResendMailConfigurator::hydrate(true);

        $fromAddress = (string) (config('mail.from.address') ?: 'onboarding@resend.dev');
        $fromName = (string) (config('mail.from.name') ?: 'CandyCutz Luxury Grooming');
        if (in_array(strtolower($fromName), ['candycutz', 'candycutz project', 'laravel'], true)) {
            $fromName = 'CandyCutz Luxury Grooming';
        }

        $viewData = [
            'recipient' => $to,
            'fromAddress' => $fromAddress,
            'fromName' => $fromName,
            'dispatchedAt' => now()->format('M d, Y • g:i A T'),
            'environment' => app()->environment(),
            'adminUrl' => rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/').'/admin/integrations/resend',
        ];

        try {
            Mail::send('mail.connection-test', $viewData, function ($message) use ($to, $fromAddress, $fromName) {
                $message->to($to)
                    ->from($fromAddress, $fromName)
                    ->subject('CandyCutz — Resend Connection Test');
            });

            if ($delivery) {
                $delivery->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'provider_reference' => 'resend-test-'.uniqid(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('Resend test email failed', [
                'recipient' => $to,
                'error' => $exception->getMessage(),
            ]);

            try {
                app(ErrorReporter::class)->report($exception);
            } catch (\Throwable) {
                // Safeguard against error reporter failures
            }

            if ($delivery) {
                $delivery->update([
                    'status' => 'failed',
                    'failure_code' => 'EMAIL_FAILED',
                    'failure_reason' => $exception->getMessage(),
                ]);
            }

            $rawMsg = $exception->getMessage();
            $userMessage = $rawMsg ?: 'Unable to send test email. Please check your Resend API key and verified sending domain in your Resend dashboard.';

            return ApiResponse::error(
                $userMessage,
                ['error_details' => $rawMsg],
                422,
                'EMAIL_FAILED'
            );
        }

        return ApiResponse::success([
            'sent_to' => $to,
            'delivery_id' => $delivery?->id,
        ], 'Test email sent successfully');
    }

    public function bankDetails(): JsonResponse
    {
        $bankName = Setting::query()->where('key', 'bank_name')->value('value')
            ?? Setting::query()->where('key', 'manual_transfer_bank')->value('value')
            ?? config('payments.gateways.manual_transfer.default_bank_name', 'Sterling Bank');

        $accountName = Setting::query()->where('key', 'account_name')->value('value')
            ?? Setting::query()->where('key', 'manual_transfer_account_name')->value('value')
            ?? config('payments.gateways.manual_transfer.default_account_name', 'NextGen PG');

        $accountNumber = Setting::query()->where('key', 'account_number')->value('value')
            ?? Setting::query()->where('key', 'manual_transfer_account_number')->value('value')
            ?? config('payments.gateways.manual_transfer.default_account_number', '5261935770');

        $instructions = Setting::query()->where('key', 'payment_instructions')->value('value')
            ?? 'Please include your Booking Reference in your transfer remarks to speed up instant confirmation.';

        $gatewayTitle = Setting::query()->where('key', 'payment_gateway_title')->value('value')
            ?? 'MTN FlyX Registration • CandyCutz';

        $expiryMinutes = (int) (Setting::query()->where('key', 'payment_expiry_minutes')->value('value') ?? 120);

        $noticeText = Setting::query()->where('key', 'payment_notice_text')->value('value')
            ?? 'Before transferring, make sure the account name shown by your bank matches the one provided below.';

        $rawChannels = Setting::query()->where('key', 'payment_channels')->value('value');
        $channels = $rawChannels ? json_decode($rawChannels, true) : ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'];

        $ussdTemplate = Setting::query()->where('key', 'ussd_code_template')->value('value')
            ?? '*737*2*{amount}*{account_number}#';

        $autoConfirm = filter_var(Setting::query()->where('key', 'payment_auto_confirm')->value('value') ?? true, FILTER_VALIDATE_BOOLEAN);

        return ApiResponse::success([
            'bank_name' => (string) $bankName,
            'account_name' => (string) $accountName,
            'account_number' => (string) $accountNumber,
            'instructions' => (string) $instructions,
            'payment_gateway_title' => (string) $gatewayTitle,
            'payment_expiry_minutes' => $expiryMinutes,
            'payment_notice_text' => (string) $noticeText,
            'payment_channels' => is_array($channels) ? $channels : ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'],
            'ussd_code_template' => (string) $ussdTemplate,
            'auto_confirm' => (bool) $autoConfirm,
        ], 'Bank account details loaded');
    }

    public function updateBankDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:120'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:50'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'payment_gateway_title' => ['nullable', 'string', 'max:150'],
            'payment_expiry_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'payment_notice_text' => ['nullable', 'string', 'max:500'],
            'payment_channels' => ['nullable', 'array'],
            'payment_channels.*' => ['string', 'in:bank_transfer,ussd,bank_account,nqr,card'],
            'ussd_code_template' => ['nullable', 'string', 'max:100'],
            'auto_confirm' => ['nullable', 'boolean'],
        ]);

        Setting::updateOrCreate(['key' => 'bank_name'], ['value' => $validated['bank_name'], 'group' => 'payment']);
        Setting::updateOrCreate(['key' => 'account_name'], ['value' => $validated['account_name'], 'group' => 'payment']);
        Setting::updateOrCreate(['key' => 'account_number'], ['value' => $validated['account_number'], 'group' => 'payment']);

        // Keep manual_transfer_* aliases in sync
        Setting::updateOrCreate(['key' => 'manual_transfer_bank'], ['value' => $validated['bank_name'], 'group' => 'payment']);
        Setting::updateOrCreate(['key' => 'manual_transfer_account_name'], ['value' => $validated['account_name'], 'group' => 'payment']);
        Setting::updateOrCreate(['key' => 'manual_transfer_account_number'], ['value' => $validated['account_number'], 'group' => 'payment']);

        if (array_key_exists('instructions', $validated)) {
            Setting::updateOrCreate(['key' => 'payment_instructions'], ['value' => $validated['instructions'] ?? '', 'group' => 'payment']);
        }

        if (array_key_exists('payment_gateway_title', $validated) && $validated['payment_gateway_title'] !== null) {
            Setting::updateOrCreate(['key' => 'payment_gateway_title'], ['value' => $validated['payment_gateway_title'], 'group' => 'payment']);
        }

        if (array_key_exists('payment_expiry_minutes', $validated) && $validated['payment_expiry_minutes'] !== null) {
            Setting::updateOrCreate(['key' => 'payment_expiry_minutes'], ['value' => (string) $validated['payment_expiry_minutes'], 'group' => 'payment']);
        }

        if (array_key_exists('payment_notice_text', $validated) && $validated['payment_notice_text'] !== null) {
            Setting::updateOrCreate(['key' => 'payment_notice_text'], ['value' => $validated['payment_notice_text'], 'group' => 'payment']);
        }

        if (array_key_exists('payment_channels', $validated) && is_array($validated['payment_channels'])) {
            Setting::updateOrCreate(['key' => 'payment_channels'], ['value' => json_encode($validated['payment_channels']), 'group' => 'payment']);
        }

        if (array_key_exists('ussd_code_template', $validated) && $validated['ussd_code_template'] !== null) {
            Setting::updateOrCreate(['key' => 'ussd_code_template'], ['value' => $validated['ussd_code_template'], 'group' => 'payment']);
        }

        if (array_key_exists('auto_confirm', $validated)) {
            Setting::updateOrCreate(['key' => 'payment_auto_confirm'], ['value' => $validated['auto_confirm'] ? '1' : '0', 'group' => 'payment']);
        }

        return ApiResponse::success([
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'instructions' => $validated['instructions'] ?? '',
            'payment_gateway_title' => $validated['payment_gateway_title'] ?? Setting::query()->where('key', 'payment_gateway_title')->value('value') ?? 'MTN FlyX Registration • CandyCutz',
            'payment_expiry_minutes' => (int) ($validated['payment_expiry_minutes'] ?? Setting::query()->where('key', 'payment_expiry_minutes')->value('value') ?? 120),
            'payment_notice_text' => $validated['payment_notice_text'] ?? Setting::query()->where('key', 'payment_notice_text')->value('value') ?? 'Before transferring, make sure the account name shown by your bank matches the one provided below.',
            'payment_channels' => $validated['payment_channels'] ?? ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'],
            'ussd_code_template' => $validated['ussd_code_template'] ?? '*737*2*{amount}*{account_number}#',
            'auto_confirm' => array_key_exists('auto_confirm', $validated) ? (bool) $validated['auto_confirm'] : true,
        ], 'Bank account details saved to database successfully');
    }

    public function getPaystackSettings(): JsonResponse
    {
        $publicKey = Setting::query()->where('key', 'paystack_public_key')->value('value') ?? config('services.paystack.public_key', '');
        $secretKey = Setting::query()->where('key', 'paystack_secret_key')->value('value') ?? config('services.paystack.secret_key', '');

        $maskedSecret = '';
        if (! empty($secretKey)) {
            $len = strlen($secretKey);
            $maskedSecret = $len > 10 ? substr($secretKey, 0, 7).'...'.substr($secretKey, -4) : '********';
        }

        $appUrl = rtrim(config('app.url') ?? 'http://localhost:8000', '/');
        $webhookUrl = $appUrl.'/api/v1/payments/webhook';

        $defaultGateway = Setting::query()->where('key', 'payment_default_gateway')->value('value')
            ?: config('payments.default', 'paystack');

        return ApiResponse::success([
            'public_key' => $publicKey,
            'is_secret_set' => ! empty($secretKey),
            'masked_secret_key' => $maskedSecret,
            'webhook_url' => $webhookUrl,
            'is_live' => str_starts_with((string) $publicKey, 'pk_live_'),
            'default_gateway' => $defaultGateway,
        ], 'Paystack settings retrieved');
    }

    public function updatePaystackSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'public_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'default_gateway' => ['nullable', 'string', 'in:paystack,manual_transfer'],
        ]);

        if (array_key_exists('public_key', $validated) && $validated['public_key'] !== null) {
            Setting::updateOrCreate(
                ['key' => 'paystack_public_key'],
                ['value' => trim($validated['public_key']), 'group' => 'payment']
            );
        }

        if (! empty($validated['secret_key'])) {
            Setting::updateOrCreate(
                ['key' => 'paystack_secret_key'],
                ['value' => trim($validated['secret_key']), 'group' => 'payment']
            );
        }

        if (! empty($validated['default_gateway'])) {
            Setting::updateOrCreate(
                ['key' => 'payment_default_gateway'],
                ['value' => trim($validated['default_gateway']), 'group' => 'payment']
            );
        }

        Cache::forget('paystack_gateway_credentials');

        return ApiResponse::success(null, 'Paystack settings updated successfully');
    }

    public function unlinkGoogleAccount(int $id): JsonResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return ApiResponse::error('Customer account not found.', [], 404);
        }
        if (strtolower((string) $user->auth_provider) !== 'google') {
            return ApiResponse::error('This customer is not linked to Google.', [], 422);
        }

        $oldProvider = $user->auth_provider;
        $user->update([
            'auth_provider' => 'local',
            'provider_id' => null,
        ]);

        AuditLogger::log(
            'google_account_unlinked',
            'GoogleAuthentication',
            $user,
            ['auth_provider' => $oldProvider, 'provider_id' => 'present'],
            ['auth_provider' => 'local', 'provider_id' => null]
        );

        return ApiResponse::success([
            'id' => $user->id,
            'auth_provider' => 'local',
        ], 'Google account link removed. Reset the local password before password login.');
    }

    public function testGoogleAuthConnection(Request $request): JsonResponse
    {
        $enabled = filter_var(
            Setting::query()->where('key', 'google_auth_enabled')->value('value') ?? true,
            FILTER_VALIDATE_BOOLEAN
        );
        if (! $enabled) {
            return ApiResponse::error(
                'Google sign-in is disabled. Enable it before testing the connection.',
                ['enabled' => false],
                422
            );
        }

        $clientId = trim((string) ($request->input('client_id')
            ?: Setting::query()->where('key', 'google_client_id')->value('value')
            ?: config('services.google.client_id', '')));

        if ($clientId === '') {
            return ApiResponse::error(
                'Google Client ID is not configured. Save the Web Client ID before testing.',
                [],
                422
            );
        }

        if (! str_ends_with($clientId, '.apps.googleusercontent.com')) {
            return ApiResponse::error(
                'The saved value is not a valid Google OAuth Web Client ID.',
                [],
                422
            );
        }

        try {
            $response = Http::timeout(10)->get(
                'https://accounts.google.com/.well-known/openid-configuration'
            );

            if (! $response->successful()) {
                return ApiResponse::error(
                    'Google could not be reached for the connection test.',
                    ['http_status' => $response->status()],
                    502
                );
            }

            $discovery = $response->json();
            if (! is_array($discovery)
                || ($discovery['issuer'] ?? null) !== 'https://accounts.google.com'
                || empty($discovery['jwks_uri'])
                || empty($discovery['authorization_endpoint'])
                || empty($discovery['token_endpoint'])
            ) {
                return ApiResponse::error(
                    'Google returned an incomplete OAuth discovery configuration.',
                    [],
                    502
                );
            }

            return ApiResponse::success([
                'connected' => true,
                'client_id' => $clientId,
                'issuer' => $discovery['issuer'],
                'authorization_endpoint' => $discovery['authorization_endpoint'],
                'token_endpoint' => $discovery['token_endpoint'],
                'message' => 'Google OAuth configuration and Google service reachability verified.',
            ], 'Google connection successful');
        } catch (\Throwable $e) {
            return ApiResponse::error(
                'Failed to reach Google OAuth services: '.$e->getMessage(),
                [],
                500
            );
        }
    }

    public function testPaystackConnection(Request $request): JsonResponse
    {
        $secretKey = $request->input('secret_key');
        if (empty($secretKey)) {
            $secretKey = Setting::query()->where('key', 'paystack_secret_key')->value('value') ?? config('services.paystack.secret_key');
        }

        if (empty($secretKey)) {
            return ApiResponse::error('Paystack Secret Key is not configured. Please enter or save your secret key first.', [], 422);
        }

        try {
            $response = Http::withToken($secretKey)
                ->timeout(10)
                ->get('https://api.paystack.co/integration/payment_session_timeout');

            if ($response->successful()) {
                $data = $response->json();
                $isLive = str_starts_with($secretKey, 'sk_live_');

                // Try to get balance info if available
                $balanceInfo = null;
                try {
                    $balRes = Http::withToken($secretKey)->timeout(5)->get('https://api.paystack.co/balance');
                    if ($balRes->successful()) {
                        $balanceInfo = $balRes->json('data');
                    }
                } catch (\Throwable) {
                    // Non-critical if balance read fails
                }

                return ApiResponse::success([
                    'connected' => true,
                    'mode' => $isLive ? 'live' : 'test',
                    'message' => 'Paystack connection verified successfully in '.($isLive ? 'LIVE' : 'TEST').' mode.',
                    'details' => $data['data'] ?? null,
                    'balance' => $balanceInfo,
                ], 'Paystack connection successful');
            }

            $errorMessage = $response->json('message') ?? 'Invalid API key or unauthorized';

            return ApiResponse::error('Paystack connection failed: '.$errorMessage, [], 400);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to reach Paystack API: '.$e->getMessage(), [], 500);
        }
    }
}
