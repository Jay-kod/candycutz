<?php

declare(strict_types=1);

use App\Models\ErrorEvent;
use App\Models\ErrorGroup;
use App\Support\Errors\AuthException;
use App\Support\Errors\BookingException;
use App\Support\Errors\ErrorCode;
use App\Support\Errors\ErrorReporter;
use App\Support\RequestContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    ErrorEvent::truncate();
    ErrorGroup::truncate();

    Route::middleware('api')->group(function () {
        Route::get('/api/v1/_test/auth-expired', function () {
            throw AuthException::tokenExpired('Your token has expired.');
        });

        Route::get('/api/v1/_test/slot-taken', function () {
            throw BookingException::slotTaken('That slot is gone.', ['slot_id' => 123]);
        });

        Route::get('/api/v1/_test/validation-error', function () {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
            ]);
        });

        Route::get('/api/v1/_test/not-found', function () {
            throw new NotFoundHttpException('Item does not exist.');
        });

        Route::get('/api/v1/_test/unhandled-500', function () {
            throw new RuntimeException('Secret database connection string in error');
        });

        Route::post('/api/v1/_test/sensitive-input', function (Request $request) {
            throw new RuntimeException('Failed while processing payment or login');
        });
    });
});

it('renders typed AuthException with standard envelope, request_id, and records error group and event', function () {
    $response = $this->withHeader('X-Request-Id', 'req_test123456789012345678')
        ->getJson('/api/v1/_test/auth-expired');

    $response->assertStatus(401);
    $response->assertHeader('X-Request-Id', 'req_test123456789012345678');

    $response->assertJson([
        'success' => false,
        'message' => 'Your token has expired.',
        'error' => [
            'code' => ErrorCode::AUTH_TOKEN_EXPIRED->value,
            'message' => 'Your token has expired.',
            'category' => 'auth',
            'action' => 'logout',
            'retryable' => false,
            'request_id' => 'req_test123456789012345678',
            'docs' => '/docs/errors#AUTH_TOKEN_EXPIRED',
        ],
        'request_id' => 'req_test123456789012345678',
        'code' => 401,
    ]);

    expect(ErrorGroup::count())->toBe(1);
    expect(ErrorEvent::count())->toBe(1);

    $group = ErrorGroup::first();
    expect($group->error_code)->toBe(ErrorCode::AUTH_TOKEN_EXPIRED->value);
    expect($group->category)->toBe('auth');
    expect($group->occurrences)->toBe(1);

    $event = ErrorEvent::first();
    expect($event->request_id)->toBe('req_test123456789012345678');
    expect($event->http_status)->toBe(401);
});

it('renders BookingException with context and action fix_input', function () {
    $response = $this->getJson('/api/v1/_test/slot-taken');

    $response->assertStatus(409);
    $response->assertJsonPath('error.code', ErrorCode::BOOKING_SLOT_TAKEN->value);
    $response->assertJsonPath('error.action', 'fix_input');
    $response->assertJsonPath('error.category', 'booking');
    $response->assertJsonPath('error.details.context.slot_id', 123);

    expect(ErrorGroup::where('error_code', ErrorCode::BOOKING_SLOT_TAKEN->value)->count())->toBe(1);
    expect(ErrorEvent::count())->toBe(1);
});

it('renders ValidationException with field errors in details', function () {
    $response = $this->getJson('/api/v1/_test/validation-error');

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', ErrorCode::VALIDATION_FAILED->value);
    $response->assertJsonPath('error.action', 'fix_input');
    $response->assertJsonPath('error.details.field_errors.email.0', 'The email field is required.');
});

it('masks unhandled 500 error messages in production mode while storing trace in event', function () {
    $originalEnv = app()->environment();

    // Force production environment
    app()->detectEnvironment(fn () => 'production');

    $response = $this->getJson('/api/v1/_test/unhandled-500');

    $response->assertStatus(500);
    $response->assertJsonPath('error.code', ErrorCode::SERVER_ERROR->value);
    $response->assertJsonPath('error.action', 'contact_support');

    // Message must NOT leak internal secret string
    expect($response->json('message'))->not->toContain('Secret database connection string');
    expect($response->json('error.message'))->not->toContain('Secret database connection string');

    // But stored ErrorEvent must preserve it for operators
    $event = ErrorEvent::where('exception_class', RuntimeException::class)->first();
    expect($event)->not->toBeNull();
    expect($event->message)->toContain('Secret database connection string');
    expect($event->trace)->not->toBeEmpty();

    // Restore environment
    app()->detectEnvironment(fn () => $originalEnv);
});

it('redacts sensitive credentials such as password, token, and receipts from stored error events', function () {
    $fakeFile = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    $response = $this->postJson('/api/v1/_test/sensitive-input', [
        'email' => 'client@candycutz.com',
        'password' => 'hunter2',
        'token' => 'super_secret_jwt_token_value',
        'card' => '4111111111111111',
        'receipt' => $fakeFile,
        'safe_note' => 'Please cut it fresh',
    ]);

    $response->assertStatus(500);

    $event = ErrorEvent::first();
    expect($event)->not->toBeNull();

    $input = $event->context['request_input'] ?? [];

    // Sensitive keys must be redacted
    expect($input['password'] ?? null)->toBe('[REDACTED]');
    expect($input['token'] ?? null)->toBe('[REDACTED]');
    expect($input['card'] ?? null)->toBe('[REDACTED]');
    expect($input['receipt'] ?? null)->toBe('[REDACTED]');
    expect($input['safe_note'] ?? null)->toBe('Please cut it fresh');

    // Ensure raw secret string never appears in entire context JSON
    $rawContext = json_encode($event->context);
    expect($rawContext)->not->toContain('hunter2');
    expect($rawContext)->not->toContain('super_secret_jwt_token_value');
    expect($rawContext)->not->toContain('4111111111111111');
});

it('replays fallback error log entries into error_groups and error_events tables', function () {
    $logDir = storage_path('logs');
    $date = Carbon::now()->format('Y-m-d');
    $testLogFile = "{$logDir}/error-fallback-{$date}.log";

    $dummyEntry = [
        'timestamp' => Carbon::now()->toIso8601String(),
        'fallback_reason' => 'Database connection offline during report',
        'error_code' => ErrorCode::DB_UNAVAILABLE->value,
        'exception_class' => 'Illuminate\\Database\\QueryException',
        'message' => 'SQLSTATE[HY000] [2002] Connection refused',
        'file' => 'vendor/laravel/framework/src/Illuminate/Database/Connection.php',
        'line' => 700,
        'request_id' => 'req_fallback_test_9999',
        'route_uri' => 'api/v1/appointments',
    ];

    File::put($testLogFile, json_encode($dummyEntry)."\n");

    expect(File::exists($testLogFile))->toBeTrue();

    // Run replay command
    $this->artisan('errors:replay')->assertSuccessful();

    // Verify imported
    expect(ErrorGroup::where('error_code', ErrorCode::DB_UNAVAILABLE->value)->exists())->toBeTrue();
    $event = ErrorEvent::where('request_id', 'req_fallback_test_9999')->first();
    expect($event)->not->toBeNull();
    expect($event->message)->toContain('Connection refused');

    // File must be cleaned up
    expect(File::exists($testLogFile))->toBeFalse();
});
