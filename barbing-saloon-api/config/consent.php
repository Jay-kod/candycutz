<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Consent enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled, customers must accept the current Terms of Service before
    | any authenticated customer endpoint is available. Authentication, logout,
    | health checks and the consent endpoints themselves stay reachable.
    |
    */
    'enforce' => env('CONSENT_ENFORCE', true),

    /*
    | Roles that must accept the Terms of Service. Admins, super admins and
    | barbers are intentionally excluded from this first rollout.
    */
    'enforce_roles' => ['customer'],

    /*
    |--------------------------------------------------------------------------
    | Current policy versions
    |--------------------------------------------------------------------------
    |
    | A matching key in the settings table overrides these values. Bump the
    | version whenever the legal content changes so users must re-accept.
    |
    */
    'terms_version' => '2026-11-26',
    'privacy_version' => '2026-11-26',
    'cookie_policy_version' => '2026-11-26',

    'documents' => [
        'terms' => '/terms',
        'privacy' => '/privacy',
        'cookies' => '/cookies',
    ],
];
