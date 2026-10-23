<?php

declare(strict_types=1);

/**
 * Whitelist of unauthenticated (public) routes for the CandyCutz API.
 * Any route NOT in this whitelist MUST require authentication and proper gate enforcement.
 */
return [
    'routes' => [
        // Health & Diagnostics
        'api/v1/health',
        'api/v1/health/ready',
        'api/v1/system/client-errors',

        // Authentication Entrypoints
        'api/v1/auth/login',
        'api/v1/auth/register',
        'api/v1/auth/forgot-password',
        'api/v1/auth/reset-password',
        'api/v1/auth/email/verify/{id}/{hash}',
        'api/v1/auth/social',
        'api/v1/auth/social-login',
        'api/v1/private-media/users/{user}/{type}/{filename}',

        // Public Catalog & Content
        'api/v1/services',
        'api/v1/service-categories',
        'api/v1/services/{idOrSlug}',
        'api/v1/barbers',
        'api/v1/barbers/{id}',
        'api/v1/barbers/{idOrSlug}',
        'api/v1/gallery',
        'api/v1/gallery/{id}',
        'api/v1/gallery-categories',
        'api/v1/testimonials',
        'api/v1/blog',
        'api/v1/blog/{slug}',
        'api/v1/working-hours',
        'api/v1/availability',
        'api/v1/available-slots',
        'api/v1/service-zones',
        'api/v1/settings',
        'api/v1/animations',
        'api/v1/contact',
        'api/v1/feature-flags',
        'api/v1/app/crashes',

        // Public Namespaced Aliases (/api/v1/public/*)
        'api/v1/public/settings',
        'api/v1/public/animations',
        'api/v1/public/services',
        'api/v1/public/service-categories',
        'api/v1/public/services/{idOrSlug}',
        'api/v1/public/barbers',
        'api/v1/public/barbers/{id}',
        'api/v1/public/service-zones',
        'api/v1/public/gallery',
        'api/v1/public/gallery/{id}',
        'api/v1/public/testimonials',
        'api/v1/public/blog',
        'api/v1/public/blog/{slug}',
        'api/v1/public/working-hours',
        'api/v1/public/available-slots',
        'api/v1/public/contact',
        'api/v1/public/feature-flags',

        // Webhooks & Payment Returns
        'api/v1/payments/webhook',
        'api/v1/payments/return',
        'pay/return',

        // Infrastructure & Web root
        'sanctum/csrf-cookie',
        'up',
        '/',

        // Legacy 410 Gone Fallbacks
        'api/customer/{any?}',
        'api/barber/{any?}',
        'api/admin/{any?}',
        'api/super_admin/{any?}',
    ],
];
