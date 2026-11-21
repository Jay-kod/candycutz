<?php

declare(strict_types=1);

namespace App\Support\Errors;

enum ErrorCode: string
{
    // Auth & Identity
    case AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    case AUTH_TOKEN_INVALID = 'AUTH_TOKEN_INVALID';
    case AUTH_TOKEN_EXPIRED = 'AUTH_TOKEN_EXPIRED';
    case AUTH_TOKEN_REVOKED = 'AUTH_TOKEN_REVOKED';
    case AUTH_ACCOUNT_SUSPENDED = 'AUTH_ACCOUNT_SUSPENDED';
    case AUTH_FORBIDDEN_ROLE = 'AUTH_FORBIDDEN_ROLE';
    case AUTH_FORBIDDEN_POLICY = 'AUTH_FORBIDDEN_POLICY';
    case AUTH_SOCIAL_VERIFICATION_FAILED = 'AUTH_SOCIAL_VERIFICATION_FAILED';
    case AUTH_SOCIAL_EMAIL_UNVERIFIED = 'AUTH_SOCIAL_EMAIL_UNVERIFIED';
    case AUTH_SOCIAL_LINK_FORBIDDEN = 'AUTH_SOCIAL_LINK_FORBIDDEN';
    case AUTH_RATE_LIMITED = 'AUTH_RATE_LIMITED';

    // Rate Limiting & Validation
    case RATE_LIMITED = 'RATE_LIMITED';
    case VALIDATION_FAILED = 'VALIDATION_FAILED';

    // Booking & Appointments
    case BOOKING_SLOT_TAKEN = 'BOOKING_SLOT_TAKEN';
    case BOOKING_OUTSIDE_HOURS = 'BOOKING_OUTSIDE_HOURS';
    case BOOKING_HOLIDAY = 'BOOKING_HOLIDAY';
    case BOOKING_NOTICE_TOO_SHORT = 'BOOKING_NOTICE_TOO_SHORT';
    case BOOKING_BARBER_UNAVAILABLE = 'BOOKING_BARBER_UNAVAILABLE';
    case BOOKING_INVALID_TRANSITION = 'BOOKING_INVALID_TRANSITION';
    case BOOKING_FAILED = 'BOOKING_FAILED';

    // Idempotency
    case IDEMPOTENCY_KEY_REUSED = 'IDEMPOTENCY_KEY_REUSED';

    // Payments
    case PAYMENT_WEBHOOK_SIGNATURE_INVALID = 'PAYMENT_WEBHOOK_SIGNATURE_INVALID';
    case PAYMENT_WEBHOOK_NOT_CONFIGURED = 'PAYMENT_WEBHOOK_NOT_CONFIGURED';
    case PAYMENT_PROVIDER_ERROR = 'PAYMENT_PROVIDER_ERROR';

    // Uploads
    case UPLOAD_INVALID = 'UPLOAD_INVALID';

    // Resources & Routing
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    case ROUTE_NOT_FOUND = 'ROUTE_NOT_FOUND';

    // System, Version & Features
    case FEATURE_DISABLED = 'FEATURE_DISABLED';
    case APP_UPDATE_REQUIRED = 'APP_UPDATE_REQUIRED';

    // Database & Infrastructure
    case DB_UNAVAILABLE = 'DB_UNAVAILABLE';
    case DB_DEADLOCK = 'DB_DEADLOCK';
    case DB_CONSTRAINT_VIOLATION = 'DB_CONSTRAINT_VIOLATION';
    case SCHEMA_DRIFT = 'SCHEMA_DRIFT';
    case QUEUE_UNAVAILABLE = 'QUEUE_UNAVAILABLE';
    case CACHE_UNAVAILABLE = 'CACHE_UNAVAILABLE';
    case EXTERNAL_SERVICE_ERROR = 'EXTERNAL_SERVICE_ERROR';
    case SERVER_ERROR = 'SERVER_ERROR';

    public function status(): int
    {
        return match ($this) {
            self::AUTH_INVALID_CREDENTIALS,
            self::AUTH_TOKEN_INVALID,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_TOKEN_REVOKED => 401,

            self::AUTH_ACCOUNT_SUSPENDED,
            self::AUTH_FORBIDDEN_ROLE,
            self::AUTH_FORBIDDEN_POLICY,
            self::AUTH_SOCIAL_LINK_FORBIDDEN => 403,

            self::AUTH_SOCIAL_VERIFICATION_FAILED,
            self::AUTH_SOCIAL_EMAIL_UNVERIFIED,
            self::PAYMENT_WEBHOOK_SIGNATURE_INVALID => 400,

            self::RESOURCE_NOT_FOUND,
            self::ROUTE_NOT_FOUND => 404,

            self::METHOD_NOT_ALLOWED => 405,

            self::BOOKING_SLOT_TAKEN,
            self::BOOKING_INVALID_TRANSITION,
            self::IDEMPOTENCY_KEY_REUSED,
            self::DB_CONSTRAINT_VIOLATION => 409,

            self::VALIDATION_FAILED,
            self::BOOKING_OUTSIDE_HOURS,
            self::BOOKING_HOLIDAY,
            self::BOOKING_NOTICE_TOO_SHORT,
            self::BOOKING_BARBER_UNAVAILABLE,
            self::UPLOAD_INVALID => 422,

            self::APP_UPDATE_REQUIRED => 426,

            self::AUTH_RATE_LIMITED,
            self::RATE_LIMITED => 429,

            self::PAYMENT_PROVIDER_ERROR,
            self::EXTERNAL_SERVICE_ERROR => 502,

            self::PAYMENT_WEBHOOK_NOT_CONFIGURED,
            self::FEATURE_DISABLED,
            self::DB_UNAVAILABLE,
            self::DB_DEADLOCK,
            self::QUEUE_UNAVAILABLE,
            self::CACHE_UNAVAILABLE => 503,

            self::BOOKING_FAILED,
            self::SCHEMA_DRIFT,
            self::SERVER_ERROR => 500,
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::AUTH_INVALID_CREDENTIALS,
            self::AUTH_TOKEN_INVALID,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_TOKEN_REVOKED,
            self::AUTH_ACCOUNT_SUSPENDED,
            self::AUTH_FORBIDDEN_ROLE,
            self::AUTH_FORBIDDEN_POLICY,
            self::AUTH_SOCIAL_VERIFICATION_FAILED,
            self::AUTH_SOCIAL_EMAIL_UNVERIFIED,
            self::AUTH_SOCIAL_LINK_FORBIDDEN => 'auth',

            self::AUTH_RATE_LIMITED,
            self::RATE_LIMITED => 'rate_limit',

            self::VALIDATION_FAILED => 'validation',

            self::BOOKING_SLOT_TAKEN,
            self::BOOKING_OUTSIDE_HOURS,
            self::BOOKING_HOLIDAY,
            self::BOOKING_NOTICE_TOO_SHORT,
            self::BOOKING_BARBER_UNAVAILABLE,
            self::BOOKING_INVALID_TRANSITION,
            self::BOOKING_FAILED => 'booking',

            self::IDEMPOTENCY_KEY_REUSED => 'idempotency',

            self::PAYMENT_WEBHOOK_SIGNATURE_INVALID,
            self::PAYMENT_WEBHOOK_NOT_CONFIGURED,
            self::PAYMENT_PROVIDER_ERROR => 'payment',

            self::UPLOAD_INVALID => 'upload',

            self::RESOURCE_NOT_FOUND,
            self::METHOD_NOT_ALLOWED,
            self::ROUTE_NOT_FOUND => 'routing',

            self::FEATURE_DISABLED,
            self::APP_UPDATE_REQUIRED => 'app',

            self::DB_UNAVAILABLE,
            self::DB_DEADLOCK,
            self::DB_CONSTRAINT_VIOLATION,
            self::SCHEMA_DRIFT,
            self::QUEUE_UNAVAILABLE,
            self::CACHE_UNAVAILABLE,
            self::EXTERNAL_SERVICE_ERROR,
            self::SERVER_ERROR => 'infra',
        };
    }

    public function severity(): string
    {
        return match ($this) {
            self::AUTH_INVALID_CREDENTIALS,
            self::AUTH_TOKEN_INVALID,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_TOKEN_REVOKED,
            self::AUTH_ACCOUNT_SUSPENDED,
            self::VALIDATION_FAILED,
            self::BOOKING_SLOT_TAKEN,
            self::BOOKING_OUTSIDE_HOURS,
            self::BOOKING_HOLIDAY,
            self::BOOKING_NOTICE_TOO_SHORT,
            self::BOOKING_BARBER_UNAVAILABLE,
            self::UPLOAD_INVALID,
            self::RESOURCE_NOT_FOUND,
            self::METHOD_NOT_ALLOWED,
            self::ROUTE_NOT_FOUND,
            self::FEATURE_DISABLED => 'info',

            self::AUTH_FORBIDDEN_ROLE,
            self::AUTH_FORBIDDEN_POLICY,
            self::AUTH_SOCIAL_VERIFICATION_FAILED,
            self::AUTH_SOCIAL_EMAIL_UNVERIFIED,
            self::AUTH_SOCIAL_LINK_FORBIDDEN,
            self::AUTH_RATE_LIMITED,
            self::RATE_LIMITED,
            self::BOOKING_INVALID_TRANSITION,
            self::IDEMPOTENCY_KEY_REUSED,
            self::APP_UPDATE_REQUIRED,
            self::DB_DEADLOCK => 'warning',

            self::BOOKING_FAILED,
            self::PAYMENT_PROVIDER_ERROR,
            self::DB_CONSTRAINT_VIOLATION,
            self::QUEUE_UNAVAILABLE,
            self::CACHE_UNAVAILABLE,
            self::EXTERNAL_SERVICE_ERROR,
            self::SERVER_ERROR => 'error',

            self::PAYMENT_WEBHOOK_SIGNATURE_INVALID,
            self::PAYMENT_WEBHOOK_NOT_CONFIGURED,
            self::DB_UNAVAILABLE,
            self::SCHEMA_DRIFT => 'critical',
        };
    }

    public function action(): string
    {
        return match ($this) {
            self::AUTH_INVALID_CREDENTIALS,
            self::AUTH_SOCIAL_VERIFICATION_FAILED,
            self::AUTH_SOCIAL_EMAIL_UNVERIFIED,
            self::VALIDATION_FAILED,
            self::BOOKING_SLOT_TAKEN,
            self::BOOKING_OUTSIDE_HOURS,
            self::BOOKING_HOLIDAY,
            self::BOOKING_NOTICE_TOO_SHORT,
            self::BOOKING_BARBER_UNAVAILABLE,
            self::UPLOAD_INVALID => 'fix_input',

            self::AUTH_TOKEN_INVALID,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_TOKEN_REVOKED,
            self::AUTH_ACCOUNT_SUSPENDED => 'logout',

            self::AUTH_RATE_LIMITED,
            self::RATE_LIMITED => 'wait',

            self::BOOKING_FAILED,
            self::PAYMENT_PROVIDER_ERROR,
            self::DB_UNAVAILABLE,
            self::DB_DEADLOCK,
            self::QUEUE_UNAVAILABLE,
            self::CACHE_UNAVAILABLE,
            self::EXTERNAL_SERVICE_ERROR => 'retry',

            self::APP_UPDATE_REQUIRED => 'update_app',

            self::SCHEMA_DRIFT,
            self::SERVER_ERROR => 'contact_support',

            self::AUTH_FORBIDDEN_ROLE,
            self::AUTH_FORBIDDEN_POLICY,
            self::AUTH_SOCIAL_LINK_FORBIDDEN,
            self::BOOKING_INVALID_TRANSITION,
            self::IDEMPOTENCY_KEY_REUSED,
            self::PAYMENT_WEBHOOK_SIGNATURE_INVALID,
            self::PAYMENT_WEBHOOK_NOT_CONFIGURED,
            self::RESOURCE_NOT_FOUND,
            self::METHOD_NOT_ALLOWED,
            self::ROUTE_NOT_FOUND,
            self::FEATURE_DISABLED,
            self::DB_CONSTRAINT_VIOLATION => 'none',
        };
    }

    public function retryable(): bool
    {
        return match ($this) {
            self::BOOKING_FAILED,
            self::PAYMENT_PROVIDER_ERROR,
            self::DB_UNAVAILABLE,
            self::DB_DEADLOCK,
            self::QUEUE_UNAVAILABLE,
            self::CACHE_UNAVAILABLE,
            self::EXTERNAL_SERVICE_ERROR => true,

            default => false,
        };
    }

    public function userMessage(): string
    {
        return match ($this) {
            self::AUTH_INVALID_CREDENTIALS => 'Invalid email or password.',
            self::AUTH_TOKEN_INVALID => 'Your session is invalid. Please sign in again.',
            self::AUTH_TOKEN_EXPIRED => 'Your session has expired. Please sign in again.',
            self::AUTH_TOKEN_REVOKED => 'Your session was revoked. Please sign in again.',
            self::AUTH_ACCOUNT_SUSPENDED => 'Your account is currently inactive or suspended.',
            self::AUTH_FORBIDDEN_ROLE => 'You do not have permission to access this resource.',
            self::AUTH_FORBIDDEN_POLICY => 'You do not have permission to perform this action.',
            self::AUTH_SOCIAL_VERIFICATION_FAILED => 'Social authentication failed. Please try again.',
            self::AUTH_SOCIAL_EMAIL_UNVERIFIED => 'Social account email is not verified.',
            self::AUTH_SOCIAL_LINK_FORBIDDEN => 'Cannot link social provider to this account.',
            self::AUTH_RATE_LIMITED => 'Too many login attempts. Please wait before trying again.',
            self::RATE_LIMITED => 'Too many requests. Please slow down.',
            self::VALIDATION_FAILED => 'The submitted data was invalid.',
            self::BOOKING_SLOT_TAKEN => 'That time slot was just taken. Please choose another time.',
            self::BOOKING_OUTSIDE_HOURS => 'The selected time is outside shop operating hours.',
            self::BOOKING_HOLIDAY => 'The salon is closed on this date.',
            self::BOOKING_NOTICE_TOO_SHORT => 'Appointments require advance notice.',
            self::BOOKING_BARBER_UNAVAILABLE => 'The selected barber is unavailable at this time.',
            self::BOOKING_INVALID_TRANSITION => 'The appointment status cannot be changed in this way.',
            self::BOOKING_FAILED => 'Unable to complete your booking. Please try again.',
            self::IDEMPOTENCY_KEY_REUSED => 'This operation was already submitted with different details.',
            self::PAYMENT_WEBHOOK_SIGNATURE_INVALID => 'Payment verification failed.',
            self::PAYMENT_WEBHOOK_NOT_CONFIGURED => 'Payment service is temporarily unavailable.',
            self::PAYMENT_PROVIDER_ERROR => 'Payment provider encountered an error. Please try again.',
            self::UPLOAD_INVALID => 'The uploaded file was rejected. Please check size and type.',
            self::RESOURCE_NOT_FOUND => 'The requested resource was not found.',
            self::METHOD_NOT_ALLOWED => 'Method not allowed for this route.',
            self::ROUTE_NOT_FOUND => 'Endpoint not found.',
            self::FEATURE_DISABLED => 'This feature is temporarily unavailable.',
            self::APP_UPDATE_REQUIRED => 'A required app update is available. Please update your app.',
            self::DB_UNAVAILABLE => 'Database service is temporarily unavailable. Please retry shortly.',
            self::DB_DEADLOCK => 'A database conflict occurred. Please retry.',
            self::DB_CONSTRAINT_VIOLATION => 'A data conflict occurred. Please check your input.',
            self::SCHEMA_DRIFT => 'A system configuration mismatch occurred.',
            self::QUEUE_UNAVAILABLE => 'Background processing is temporarily unavailable.',
            self::CACHE_UNAVAILABLE => 'Cache system is temporarily unavailable.',
            self::EXTERNAL_SERVICE_ERROR => 'An external service failed. Please try again shortly.',
            self::SERVER_ERROR => 'An unexpected error occurred. Please contact support.',
        };
    }

    public function operatorHint(): string
    {
        return match ($this) {
            self::AUTH_INVALID_CREDENTIALS => 'Normal user error. Spike from one IP = credential stuffing → check security events.',
            self::AUTH_TOKEN_INVALID,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_TOKEN_REVOKED => 'Session dead. Many at once after deploy = APP_KEY or token table changed.',
            self::AUTH_ACCOUNT_SUSPENDED => 'Suspended in admin panel; check audit log for who/when.',
            self::AUTH_FORBIDDEN_ROLE,
            self::AUTH_FORBIDDEN_POLICY => 'Gate denied. Repeated from same user = probing; check security events.',
            self::AUTH_SOCIAL_VERIFICATION_FAILED => 'Provider token rejected. Check client ids/JWKS reachability.',
            self::AUTH_SOCIAL_EMAIL_UNVERIFIED => 'Provider claims email is not verified.',
            self::AUTH_SOCIAL_LINK_FORBIDDEN => 'Refused auto-link to high-privilege account.',
            self::AUTH_RATE_LIMITED,
            self::RATE_LIMITED => 'Limiter name is in context.limiter.',
            self::VALIDATION_FAILED => 'Field errors in details.field_errors. Frequent on one field = client/contract drift.',
            self::BOOKING_SLOT_TAKEN => 'Expected under load. Unusual volume = check lock contention.',
            self::BOOKING_OUTSIDE_HOURS,
            self::BOOKING_HOLIDAY,
            self::BOOKING_NOTICE_TOO_SHORT,
            self::BOOKING_BARBER_UNAVAILABLE => 'Rule engine denial; verify working hours/holidays data.',
            self::BOOKING_INVALID_TRANSITION => 'Client sent an illegal status change; check state machine + client build.',
            self::BOOKING_FAILED => 'Unexpected exception in booking path; open the linked trace.',
            self::IDEMPOTENCY_KEY_REUSED => 'Same key, different payload; client bug.',
            self::PAYMENT_WEBHOOK_SIGNATURE_INVALID => 'Wrong webhook secret or forged request.',
            self::PAYMENT_WEBHOOK_NOT_CONFIGURED => 'Env secret missing. Run system:preflight.',
            self::PAYMENT_PROVIDER_ERROR => 'Gateway error; see context.provider_code.',
            self::UPLOAD_INVALID => 'Type/size rejected.',
            self::RESOURCE_NOT_FOUND,
            self::METHOD_NOT_ALLOWED,
            self::ROUTE_NOT_FOUND => 'Many ROUTE_NOT_FOUND from a client build = wrong API path/version.',
            self::FEATURE_DISABLED => 'Flag off (e.g. social login).',
            self::APP_UPDATE_REQUIRED => 'Client below min_app_version.',
            self::DB_UNAVAILABLE => 'DB unreachable (SQLSTATE 2002/2006/HY000). Check db container, credentials, disk.',
            self::DB_DEADLOCK => 'SQLSTATE 40001/1213; retried automatically; frequent = lock ordering issue.',
            self::DB_CONSTRAINT_VIOLATION => 'SQLSTATE 23000 (1062 duplicate/1452 FK). Check missing idempotency or race.',
            self::SCHEMA_DRIFT => 'SQLSTATE 42S22/42S02 (unknown column/table). Migration missing: run migrate, then schema:verify.',
            self::QUEUE_UNAVAILABLE,
            self::CACHE_UNAVAILABLE => 'Redis/Cache down or misconfigured.',
            self::EXTERNAL_SERVICE_ERROR => 'Brevo/Google/Apple/Stripe HTTP failure; check context.service.',
            self::SERVER_ERROR => 'Unclassified exception. Open the group, read the trace, add a specific code.',
        };
    }
}
