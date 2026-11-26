<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Services\ConsentService;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConsentAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('consent.enforce')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        if (! in_array($role, (array) config('consent.enforce_roles', ['customer']), true)) {
            return $next($request);
        }

        if ($this->isExempt($request)) {
            return $next($request);
        }

        $consent = app(ConsentService::class);
        $version = $consent->currentTermsVersion();

        if ($consent->hasAccepted($user, 'terms', $version)) {
            return $next($request);
        }

        return ApiResponse::error(
            'You must accept the Terms of Service to continue.',
            [],
            403,
            'CONSENT_REQUIRED',
            action: 'accept_terms',
            context: [
                'terms_version' => $version,
                'terms_url' => config('consent.documents.terms', '/terms'),
                'privacy_url' => config('consent.documents.privacy', '/privacy'),
                'accept_url' => '/api/v1/account/consents/accept',
            ]
        );
    }

    private function isExempt(Request $request): bool
    {
        return $request->is(
            'api/v1/auth/*',
            'api/v1/account/consents/*',
            'api/v1/consents/*',
            'api/v1/health*',
            'api/v1/system/client-errors',
            'api/v1/payments/webhook*',
        );
    }
}
