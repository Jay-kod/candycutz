<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestContext
{
    private string $requestId;
    private ?string $client;
    private ?string $appVersion;
    private ?string $deviceId;
    private ?int $userId = null;
    private ?string $role = null;
    private ?string $ip = null;
    private ?string $userAgent = null;
    private ?string $routeUri = null;
    private ?string $method = null;
    private float $startTime;

    public function __construct(
        ?string $requestId = null,
        ?string $client = null,
        ?string $appVersion = null,
        ?string $deviceId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $routeUri = null,
        ?string $method = null
    ) {
        $this->requestId = $requestId ?? self::generateRequestId();
        $this->client = $client;
        $this->appVersion = $appVersion;
        $this->deviceId = $deviceId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->routeUri = $routeUri;
        $this->method = $method;
        $this->startTime = microtime(true);
    }

    public static function fromRequest(Request $request): self
    {
        $incomingId = $request->header('X-Request-Id');
        if (! is_string($incomingId) || ! preg_match('/^req_[A-Za-z0-9]{10,40}$/', $incomingId)) {
            $incomingId = self::generateRequestId();
        }

        $context = new self(
            requestId: $incomingId,
            client: $request->header('X-Client'),
            appVersion: $request->header('X-App-Version'),
            deviceId: $request->header('X-Device-Id'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            routeUri: $request->route()?->uri() ?? $request->path(),
            method: $request->method()
        );

        if ($user = $request->user()) {
            $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) ($user->role ?? 'customer');
            $context->setAuthUser((int) $user->id, $role);
        }

        return $context;
    }

    public static function generateRequestId(): string
    {
        // 24 random alphanumeric characters with req_ prefix
        return 'req_'.strtolower(Str::random(24));
    }

    public function setAuthUser(int $userId, string $role): self
    {
        $this->userId = $userId;
        $this->role = $role;

        return $this;
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function client(): ?string
    {
        return $this->client;
    }

    public function appVersion(): ?string
    {
        return $this->appVersion;
    }

    public function deviceId(): ?string
    {
        return $this->deviceId;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function role(): ?string
    {
        return $this->role;
    }

    public function ip(): ?string
    {
        return $this->ip;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function routeUri(): ?string
    {
        return $this->routeUri;
    }

    public function method(): ?string
    {
        return $this->method;
    }

    public function durationMs(): int
    {
        return (int) round((microtime(true) - $this->startTime) * 1000);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'request_id' => $this->requestId,
            'client' => $this->client,
            'app_version' => $this->appVersion,
            'device_id' => $this->deviceId,
            'user_id' => $this->userId,
            'role' => $this->role,
            'ip' => $this->ip,
            'route_uri' => $this->routeUri,
            'method' => $this->method,
        ];
    }
}
