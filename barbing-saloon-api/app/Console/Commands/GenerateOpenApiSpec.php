<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

class GenerateOpenApiSpec extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OpenAPI 3.0 specification and Markdown documentation from registered API routes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning registered /api/v1/* routes...');

        $routes = Route::getRoutes()->getRoutes();
        $v1Routes = [];

        /** @var RoutingRoute $route */
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/v1/')) {
                $v1Routes[] = $route;
            }
        }

        $this->info(sprintf('Found %d routes under /api/v1/*', count($v1Routes)));

        $openApi = $this->buildOpenApiStructure($v1Routes);

        // Ensure directories exist
        $docsDir = base_path('docs');
        if (! is_dir($docsDir)) {
            @mkdir($docsDir, 0755, true);
        }

        $publicDir = public_path();
        if (! is_dir($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }

        // 1. Write docs/openapi.json
        $jsonPath = $docsDir.'/openapi.json';
        $jsonContent = json_encode($openApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($jsonPath, $jsonContent);
        $this->info("Generated: {$jsonPath}");

        // 2. Write barbing-saloon-api/public/openapi.json
        $publicJsonPath = $publicDir.'/openapi.json';
        file_put_contents($publicJsonPath, $jsonContent);
        $this->info("Generated: {$publicJsonPath}");

        // 3. Generate docs/API.md
        $markdownPath = $docsDir.'/API.md';
        $markdownContent = $this->buildMarkdownDocumentation($openApi);
        file_put_contents($markdownPath, $markdownContent);
        $this->info("Generated: {$markdownPath}");

        return self::SUCCESS;
    }

    /**
     * Build the OpenAPI 3.0 specification array.
     *
     * @param  array<int, RoutingRoute>  $routes
     * @return array<string, mixed>
     */
    private function buildOpenApiStructure(array $routes): array
    {
        $paths = [];

        foreach ($routes as $route) {
            $uri = '/'.$route->uri();
            // Normalise path for OpenAPI: replace {param?} with {param}
            $openApiPath = preg_replace('/\{([a-zA-Z0-9_]+)\?\}/', '{$1}', $uri);
            if (! is_string($openApiPath)) {
                $openApiPath = $uri;
            }

            $methods = array_diff($route->methods(), ['HEAD']);
            $action = $route->getActionName();
            $middleware = (array) $route->middleware();

            $isAuth = in_array('auth:sanctum', $middleware, true);
            $roles = $this->extractRolesFromMiddleware($middleware);
            $tag = $this->determineTag($uri);

            if (! isset($paths[$openApiPath])) {
                $paths[$openApiPath] = [];
            }

            // Extract path parameters
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $openApiPath, $matches);
            $pathParams = [];
            if (! empty($matches[1])) {
                foreach ($matches[1] as $paramName) {
                    $pathParams[] = [
                        'name' => $paramName,
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => (str_contains($paramName, 'id') || str_contains($paramName, 'Id')) ? 'integer' : 'string',
                        ],
                        'description' => "Identifier for {$paramName}",
                    ];
                }
            }

            foreach ($methods as $method) {
                $lowerMethod = strtolower($method);
                $operationId = $this->generateOperationId($method, $uri, $action);

                $operation = [
                    'tags' => [$tag],
                    'summary' => $this->generateSummary($method, $uri, $action),
                    'description' => $this->generateDescription($method, $uri, $action, $roles),
                    'operationId' => $operationId,
                    'responses' => $this->generateResponses($method, $isAuth, $roles),
                ];

                if (! empty($pathParams)) {
                    $operation['parameters'] = $pathParams;
                }

                if ($isAuth) {
                    $operation['security'] = [
                        ['BearerAuth' => []],
                    ];
                }

                if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
                    $operation['requestBody'] = $this->generateRequestBody($method, $uri);
                }

                $paths[$openApiPath][$lowerMethod] = $operation;
            }
        }

        // Sort paths alphabetically
        ksort($paths);

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'CandyCutz API',
                'version' => '1.0.0',
                'description' => 'Authoritative API surface for the CandyCutz Luxury Grooming Platform in Keffi, Nasarawa State. Governed by ARCHITECTURE.md with strict /api/v1/* route scoping and standardized response envelopes.',
                'contact' => [
                    'name' => 'CandyCutz Engineering',
                    'email' => 'concierge@candycutz.com',
                ],
            ],
            'servers' => [
                [
                    'url' => '/api/v1',
                    'description' => 'Target v1 API mount point',
                ],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum',
                        'description' => 'Sanctum Personal Access Token supplied in the Authorization header: `Bearer {token}`',
                    ],
                ],
                'schemas' => $this->buildComponentSchemas(),
            ],
        ];
    }

    /**
     * @param  array<int, string>  $middleware
     * @return array<int, string>
     */
    private function extractRolesFromMiddleware(array $middleware): array
    {
        $roles = [];
        foreach ($middleware as $m) {
            if (str_starts_with($m, 'check.role:')) {
                $roles = explode(',', substr($m, strlen('check.role:')));
            }
        }

        return $roles;
    }

    private function determineTag(string $uri): string
    {
        if (str_starts_with($uri, '/api/v1/auth')) {
            return 'Auth';
        }
        if (str_starts_with($uri, '/api/v1/appointments') || str_starts_with($uri, '/api/v1/availability')) {
            return 'Booking';
        }
        if (str_starts_with($uri, '/api/v1/payments')) {
            return 'Payments';
        }
        if (str_starts_with($uri, '/api/v1/super-admin')) {
            return 'SuperAdmin';
        }
        if (str_starts_with($uri, '/api/v1/admin')) {
            return 'Admin';
        }
        if (str_starts_with($uri, '/api/v1/notifications') || str_starts_with($uri, '/api/v1/account') || str_starts_with($uri, '/api/v1/wishlist')) {
            return 'Account';
        }
        if (str_starts_with($uri, '/api/v1/health')) {
            return 'System';
        }

        return 'Catalogue';
    }

    private function generateOperationId(string $method, string $uri, string $action): string
    {
        $cleanUri = preg_replace('/[^a-zA-Z0-9]/', '_', trim($uri, '/'));

        return strtolower($method).'_'.$cleanUri;
    }

    private function generateSummary(string $method, string $uri, string $action): string
    {
        $parts = explode('@', $action);
        $methodName = end($parts);

        $readable = ucwords(str_replace(['_', '-'], ' ', preg_replace('/^api\/v1\//', '', trim($uri, '/'))));

        return "{$method} {$readable} ({$methodName})";
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function generateDescription(string $method, string $uri, string $action, array $roles): string
    {
        $desc = "Handles {$method} request to `{$uri}` via `{$action}`.";
        if (! empty($roles)) {
            $desc .= "\n\n**Authorized Roles:** ".implode(', ', array_map(fn ($r) => "`{$r}`", $roles));
        }

        return $desc;
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int|string, array<string, mixed>>
     */
    private function generateResponses(string $method, bool $isAuth, array $roles): array
    {
        $responses = [
            '200' => [
                'description' => 'Operation completed successfully.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ApiResponse',
                        ],
                    ],
                ],
            ],
        ];

        if ($method === 'POST') {
            $responses['201'] = [
                'description' => 'Resource created successfully.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ApiResponse',
                        ],
                    ],
                ],
            ];
            $responses['422'] = [
                'description' => 'Validation error.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ApiErrorResponse',
                        ],
                    ],
                ],
            ];
        }

        if ($isAuth) {
            $responses['401'] = [
                'description' => 'Unauthenticated (token missing, invalid, or expired).',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ApiErrorResponse',
                        ],
                    ],
                ],
            ];
        }

        if (! empty($roles)) {
            $responses['403'] = [
                'description' => 'Forbidden (caller role not authorized for this resource).',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ApiErrorResponse',
                        ],
                    ],
                ],
            ];
        }

        return $responses;
    }

    /**
     * @return array<string, mixed>
     */
    private function generateRequestBody(string $method, string $uri): array
    {
        // Infer schema based on URI pattern
        $schema = null;

        if (str_contains($uri, '/auth/login')) {
            $schema = ['$ref' => '#/components/schemas/LoginRequest'];
        } elseif (str_contains($uri, '/auth/register')) {
            $schema = ['$ref' => '#/components/schemas/RegisterRequest'];
        } elseif (str_contains($uri, '/auth/social-login')) {
            $schema = ['$ref' => '#/components/schemas/SocialLoginRequest'];
        } elseif (str_contains($uri, '/auth/forgot-password')) {
            $schema = ['$ref' => '#/components/schemas/ForgotPasswordRequest'];
        } elseif (str_contains($uri, '/auth/reset-password')) {
            $schema = ['$ref' => '#/components/schemas/ResetPasswordRequest'];
        } elseif (str_contains($uri, '/appointments/walk-in')) {
            $schema = ['$ref' => '#/components/schemas/WalkInRequest'];
        } elseif (str_contains($uri, '/appointments') && $method === 'POST' && ! str_contains($uri, '/cancel')) {
            $schema = ['$ref' => '#/components/schemas/CreateBookingRequest'];
        } elseif (str_contains($uri, '/cancel')) {
            $schema = ['$ref' => '#/components/schemas/CancelAppointmentRequest'];
        } elseif (str_contains($uri, '/status')) {
            $schema = ['$ref' => '#/components/schemas/UpdateStatusRequest'];
        } elseif (str_contains($uri, '/payments/checkout')) {
            $schema = ['$ref' => '#/components/schemas/InitiateCheckoutRequest'];
        } elseif (str_contains($uri, '/receipt')) {
            $schema = ['$ref' => '#/components/schemas/UploadReceiptRequest'];
        } elseif (str_contains($uri, '/notifications/device-token')) {
            $schema = ['$ref' => '#/components/schemas/RegisterDeviceTokenRequest'];
        } elseif (str_contains($uri, '/notification-settings')) {
            $schema = ['$ref' => '#/components/schemas/UpdateNotificationPreferencesRequest'];
        } elseif (str_contains($uri, '/services') && $method === 'POST') {
            $schema = ['$ref' => '#/components/schemas/CreateServiceRequest'];
        } elseif (str_contains($uri, '/schedule') && in_array($method, ['PUT', 'POST'], true)) {
            $schema = ['$ref' => '#/components/schemas/UpdateScheduleRequest'];
        }

        if (! $schema) {
            $schema = [
                'type' => 'object',
                'description' => 'Dynamic payload parameters',
            ];
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => $schema,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildComponentSchemas(): array
    {
        return [
            'ApiResponse' => [
                'type' => 'object',
                'required' => ['success', 'data'],
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true],
                    'message' => ['type' => 'string', 'example' => 'Operation successful'],
                    'data' => ['type' => 'object', 'description' => 'Target entity or collection payload'],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'current_page' => ['type' => 'integer', 'example' => 1],
                            'last_page' => ['type' => 'integer', 'example' => 1],
                            'per_page' => ['type' => 'integer', 'example' => 15],
                            'total' => ['type' => 'integer', 'example' => 1],
                        ],
                    ],
                ],
            ],
            'ApiErrorResponse' => [
                'type' => 'object',
                'required' => ['success', 'message', 'error'],
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'message' => ['type' => 'string', 'example' => 'Validation error or unauthorized'],
                    'error' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'string', 'example' => 'VALIDATION_FAILED'],
                            'details' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
            'User' => [
                'type' => 'object',
                'required' => ['id', 'name', 'username', 'email', 'role'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'name' => ['type' => 'string', 'example' => 'Ibrahim Musa'],
                    'real_name' => ['type' => 'string', 'nullable' => true],
                    'username' => ['type' => 'string', 'example' => 'ibrahim_m'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'ibrahim@candycutz.com'],
                    'phone' => ['type' => 'string', 'nullable' => true, 'example' => '+2348012345678'],
                    'role' => [
                        'type' => 'string',
                        'enum' => ['customer', 'barber', 'admin', 'super_admin'],
                        'example' => 'customer',
                    ],
                    'avatar' => ['type' => 'string', 'nullable' => true],
                    'wallet_balance' => ['type' => 'integer', 'description' => 'Balance in kobo', 'example' => 0],
                    'notification_preferences' => ['type' => 'object', 'nullable' => true],
                    'barber' => ['$ref' => '#/components/schemas/Barber'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'Customer' => [
                'type' => 'object',
                'required' => ['id', 'name'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'name' => ['type' => 'string', 'example' => 'Ibrahim Musa'],
                    'username' => ['type' => 'string', 'nullable' => true, 'example' => 'ibrahim_m'],
                    'phone' => ['type' => 'string', 'nullable' => true, 'example' => '+2348012345678'],
                    'avatar' => ['type' => 'string', 'nullable' => true],
                    'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'ibrahim@candycutz.com'],
                ],
            ],
            'Barber' => [
                'type' => 'object',
                'required' => ['id', 'user_id', 'name', 'chair_status', 'rating', 'is_active'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'user_id' => ['type' => 'integer', 'example' => 2],
                    'name' => ['type' => 'string', 'example' => 'Master Barber Chidi'],
                    'real_name' => ['type' => 'string', 'nullable' => true],
                    'username' => ['type' => 'string', 'example' => 'barber_chidi'],
                    'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true],
                    'phone' => ['type' => 'string', 'nullable' => true],
                    'chair_status' => [
                        'type' => 'string',
                        'enum' => ['free', 'busy', 'break', 'offline'],
                        'example' => 'free',
                    ],
                    'is_available' => ['type' => 'boolean', 'example' => true],
                    'is_active' => ['type' => 'boolean', 'example' => true],
                    'rating' => ['type' => 'number', 'format' => 'float', 'example' => 4.9],
                    'total_reviews' => ['type' => 'integer', 'example' => 42],
                    'experience_years' => ['type' => 'integer', 'example' => 6],
                    'bio' => ['type' => 'string', 'nullable' => true],
                    'specialties' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'example' => ['Fade', 'Beard Sculpting', 'Hot Towel Treatment'],
                    ],
                    'avatar' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'Address' => [
                'type' => 'object',
                'required' => ['street_address', 'area_landmark', 'city'],
                'properties' => [
                    'id' => ['type' => 'integer', 'nullable' => true, 'example' => 1],
                    'label' => ['type' => 'string', 'nullable' => true, 'example' => 'Home'],
                    'street_address' => ['type' => 'string', 'example' => 'Plot 12 GRA'],
                    'area_landmark' => ['type' => 'string', 'example' => 'Near University Gate'],
                    'city' => ['type' => 'string', 'example' => 'Keffi'],
                    'state' => ['type' => 'string', 'nullable' => true, 'example' => 'Nasarawa'],
                    'service_zone_id' => ['type' => 'integer', 'nullable' => true, 'example' => 1],
                    'latitude' => ['type' => 'number', 'format' => 'float', 'nullable' => true],
                    'longitude' => ['type' => 'number', 'format' => 'float', 'nullable' => true],
                ],
            ],
            'Service' => [
                'type' => 'object',
                'required' => ['id', 'name', 'price', 'duration_minutes', 'is_active'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'name' => ['type' => 'string', 'example' => 'Signature Fade & Beard Sculpt'],
                    'slug' => ['type' => 'string', 'example' => 'signature-fade-beard-sculpt'],
                    'description' => ['type' => 'string', 'example' => 'Precision skin fade with warm towel finish'],
                    'price' => ['type' => 'integer', 'description' => 'Price in minor kobo units (500000 = 5000 NGN)', 'example' => 500000],
                    'home_service_price' => ['type' => 'integer', 'nullable' => true, 'example' => 800000],
                    'duration_minutes' => ['type' => 'integer', 'example' => 45],
                    'category_id' => ['type' => 'integer', 'nullable' => true],
                    'category' => ['type' => 'string', 'nullable' => true, 'example' => 'Haircuts'],
                    'image_url' => ['type' => 'string', 'nullable' => true],
                    'is_active' => ['type' => 'boolean', 'example' => true],
                ],
            ],
            'ServiceZone' => [
                'type' => 'object',
                'required' => ['id', 'name', 'code', 'surcharge', 'is_active'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'name' => ['type' => 'string', 'example' => 'Keffi GRA & Surroundings'],
                    'code' => ['type' => 'string', 'example' => 'KEFFI_GRA'],
                    'surcharge' => ['type' => 'integer', 'description' => 'Surcharge in minor kobo units', 'example' => 150000],
                    'min_order_amount' => ['type' => 'integer', 'example' => 500000],
                    'estimated_travel_minutes' => ['type' => 'integer', 'example' => 20],
                    'is_active' => ['type' => 'boolean', 'example' => true],
                ],
            ],
            'Appointment' => [
                'type' => 'object',
                'required' => ['id', 'booking_reference', 'customer_id', 'appointment_date', 'start_time', 'end_time', 'status', 'payment_status', 'grand_total'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 101],
                    'booking_reference' => ['type' => 'string', 'example' => 'BK-2026-89421'],
                    'customer_id' => ['type' => 'integer', 'example' => 1],
                    'barber_id' => ['type' => 'integer', 'nullable' => true, 'example' => 2],
                    'service_id' => ['type' => 'integer', 'example' => 1],
                    'appointment_date' => ['type' => 'string', 'format' => 'date', 'example' => '2026-09-20'],
                    'start_time' => ['type' => 'string', 'example' => '10:00:00'],
                    'end_time' => ['type' => 'string', 'example' => '10:45:00'],
                    'appointment_type' => ['type' => 'string', 'enum' => ['in_shop', 'home_service'], 'example' => 'in_shop'],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'],
                        'example' => 'confirmed',
                    ],
                    'payment_status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'paid', 'partially_paid', 'refunded', 'failed'],
                        'example' => 'paid',
                    ],
                    'subtotal' => ['type' => 'integer', 'example' => 500000],
                    'home_service_surcharge' => ['type' => 'integer', 'example' => 0],
                    'discount_amount' => ['type' => 'integer', 'example' => 0],
                    'grand_total' => ['type' => 'integer', 'example' => 500000],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'service' => ['$ref' => '#/components/schemas/Service'],
                    'barber' => ['$ref' => '#/components/schemas/Barber'],
                    'customer' => ['$ref' => '#/components/schemas/Customer'],
                    'destination_address' => ['$ref' => '#/components/schemas/Address'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],

            'Payment' => [
                'type' => 'object',
                'required' => ['id', 'appointment_id', 'amount', 'currency', 'gateway', 'status'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 50],
                    'appointment_id' => ['type' => 'integer', 'example' => 101],
                    'amount' => ['type' => 'integer', 'description' => 'Amount in minor units (kobo)', 'example' => 500000],
                    'currency' => ['type' => 'string', 'example' => 'NGN'],
                    'gateway' => ['type' => 'string', 'enum' => ['paystack', 'manual_transfer', 'stripe'], 'example' => 'paystack'],
                    'payment_method' => ['type' => 'string', 'example' => 'card'],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['awaiting_transfer', 'receipt_uploaded', 'under_review', 'verified', 'rejected', 'successful', 'failed', 'refunded'],
                        'example' => 'successful',
                    ],
                    'transaction_reference' => ['type' => 'string', 'example' => 'PAY-2026-09-12345'],
                    'receipt_path' => ['type' => 'string', 'nullable' => true],
                    'receipt_uploaded_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'verified_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'sla_expires_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'DeviceToken' => [
                'type' => 'object',
                'required' => ['id', 'user_id', 'token', 'platform'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'user_id' => ['type' => 'integer', 'example' => 1],
                    'token' => ['type' => 'string', 'example' => 'ExponentPushToken[xxxxxxxxxxxx]'],
                    'platform' => ['type' => 'string', 'enum' => ['ios', 'android', 'web'], 'example' => 'android'],
                    'last_seen_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'Notification' => [
                'type' => 'object',
                'required' => ['id', 'title', 'message', 'is_read'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1],
                    'sender_id' => ['type' => 'integer', 'nullable' => true],
                    'recipient_type' => ['type' => 'string', 'example' => 'customer'],
                    'recipient_id' => ['type' => 'integer', 'nullable' => true],
                    'type' => ['type' => 'string', 'example' => 'booking'],
                    'title' => ['type' => 'string', 'example' => 'Appointment Confirmed'],
                    'message' => ['type' => 'string', 'example' => 'Your grooming session has been confirmed for 10:00 AM.'],
                    'related_entity_id' => ['type' => 'integer', 'nullable' => true],
                    'is_read' => ['type' => 'boolean', 'example' => false],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            // Request DTO Schemas
            'LoginRequest' => [
                'type' => 'object',
                'required' => ['identity', 'password'],
                'properties' => [
                    'identity' => ['type' => 'string', 'description' => 'Email address or username', 'example' => 'ibrahim@candycutz.com'],
                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'Secret123!'],
                ],
            ],
            'RegisterRequest' => [
                'type' => 'object',
                'required' => ['name', 'username', 'email', 'phone', 'password', 'password_confirmation'],
                'properties' => [
                    'name' => ['type' => 'string', 'example' => 'Ibrahim Musa'],
                    'username' => ['type' => 'string', 'example' => 'ibrahim_m'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'ibrahim@candycutz.com'],
                    'phone' => ['type' => 'string', 'example' => '+2348012345678'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                    'password_confirmation' => ['type' => 'string', 'format' => 'password'],
                ],
            ],
            'SocialLoginRequest' => [
                'type' => 'object',
                'required' => ['provider', 'id_token'],
                'properties' => [
                    'provider' => ['type' => 'string', 'enum' => ['google', 'apple']],
                    'id_token' => ['type' => 'string'],
                    'role' => ['type' => 'string', 'enum' => ['customer', 'barber'], 'default' => 'customer'],
                ],
            ],
            'ForgotPasswordRequest' => [
                'type' => 'object',
                'required' => ['email'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                ],
            ],
            'ResetPasswordRequest' => [
                'type' => 'object',
                'required' => ['email', 'token', 'password', 'password_confirmation'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'token' => ['type' => 'string'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                    'password_confirmation' => ['type' => 'string', 'format' => 'password'],
                ],
            ],
            'CreateBookingRequest' => [
                'type' => 'object',
                'required' => ['service_id', 'appointment_date', 'start_time', 'appointment_type'],
                'properties' => [
                    'service_id' => ['type' => 'integer', 'example' => 1],
                    'barber_id' => ['type' => 'integer', 'nullable' => true, 'example' => 2],
                    'appointment_date' => ['type' => 'string', 'format' => 'date', 'example' => '2026-09-20'],
                    'start_time' => ['type' => 'string', 'example' => '10:00:00'],
                    'appointment_type' => ['type' => 'string', 'enum' => ['in_shop', 'home_service'], 'example' => 'in_shop'],
                    'destination_address' => [
                        'type' => 'object',
                        'nullable' => true,
                        'properties' => [
                            'street_address' => ['type' => 'string', 'example' => 'Plot 12 GRA'],
                            'area_landmark' => ['type' => 'string', 'example' => 'Near University Gate'],
                            'city' => ['type' => 'string', 'example' => 'Keffi'],
                            'state' => ['type' => 'string', 'example' => 'Nasarawa'],
                            'service_zone_id' => ['type' => 'integer', 'example' => 1],
                        ],
                    ],
                    'notes' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'WalkInRequest' => [
                'type' => 'object',
                'required' => ['customer_name', 'service_id'],
                'properties' => [
                    'customer_name' => ['type' => 'string', 'example' => 'Walk-in Guest'],
                    'customer_phone' => ['type' => 'string', 'nullable' => true, 'example' => '+2348000000000'],
                    'service_id' => ['type' => 'integer', 'example' => 1],
                    'payment_method' => ['type' => 'string', 'enum' => ['cash', 'pos'], 'default' => 'cash'],
                    'notes' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'CancelAppointmentRequest' => [
                'type' => 'object',
                'properties' => [
                    'reason' => ['type' => 'string', 'example' => 'Schedule conflict'],
                ],
            ],
            'UpdateStatusRequest' => [
                'type' => 'object',
                'required' => ['status'],
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'],
                        'example' => 'in_progress',
                    ],
                    'notes' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'InitiateCheckoutRequest' => [
                'type' => 'object',
                'required' => ['appointment_id', 'gateway'],
                'properties' => [
                    'appointment_id' => ['type' => 'integer', 'example' => 101],
                    'gateway' => ['type' => 'string', 'enum' => ['paystack', 'manual_transfer', 'stripe'], 'example' => 'paystack'],
                    'callback_url' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'UploadReceiptRequest' => [
                'type' => 'object',
                'required' => ['receipt'],
                'properties' => [
                    'receipt' => ['type' => 'string', 'format' => 'binary', 'description' => 'Image or PDF proof of payment'],
                    'notes' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'RegisterDeviceTokenRequest' => [
                'type' => 'object',
                'required' => ['token'],
                'properties' => [
                    'token' => ['type' => 'string', 'example' => 'ExponentPushToken[xxxxxxxxxxxx]'],
                    'platform' => ['type' => 'string', 'enum' => ['ios', 'android', 'web'], 'default' => 'android'],
                ],
            ],
            'UpdateNotificationPreferencesRequest' => [
                'type' => 'object',
                'properties' => [
                    'notify_appointments' => ['type' => 'boolean'],
                    'notify_promotions' => ['type' => 'boolean'],
                    'notify_wishlist' => ['type' => 'boolean'],
                    'notify_blog' => ['type' => 'boolean'],
                    'notify_general' => ['type' => 'boolean'],
                ],
            ],
            'CreateServiceRequest' => [
                'type' => 'object',
                'required' => ['name', 'price', 'duration_minutes'],
                'properties' => [
                    'name' => ['type' => 'string', 'example' => 'Royal Beard Conditioning'],
                    'description' => ['type' => 'string'],
                    'price' => ['type' => 'integer', 'description' => 'Price in kobo', 'example' => 350000],
                    'home_service_price' => ['type' => 'integer', 'nullable' => true, 'example' => 600000],
                    'duration_minutes' => ['type' => 'integer', 'example' => 30],
                    'category_id' => ['type' => 'integer', 'nullable' => true],
                    'is_active' => ['type' => 'boolean', 'default' => true],
                ],
            ],
            'UpdateScheduleRequest' => [
                'type' => 'object',
                'required' => ['schedule'],
                'properties' => [
                    'schedule' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'required' => ['day_of_week', 'start_time', 'end_time'],
                            'properties' => [
                                'day_of_week' => ['type' => 'integer', 'example' => 1],
                                'day_name' => ['type' => 'string', 'example' => 'Monday'],
                                'start_time' => ['type' => 'string', 'example' => '09:00'],
                                'end_time' => ['type' => 'string', 'example' => '19:00'],
                                'is_closed' => ['type' => 'boolean', 'default' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Build the Markdown documentation string from the OpenAPI spec.
     *
     * @param  array<string, mixed>  $openApi
     */
    private function buildMarkdownDocumentation(array $openApi): string
    {
        $date = date('Y-m-d H:i:s T');
        $md = [];

        $md[] = '# CandyCutz API Reference';
        $md[] = '';
        $md[] = "<!-- THIS FILE IS AUTO-GENERATED BY 'php artisan openapi:generate'. DO NOT EDIT MANUALLY. -->";
        $md[] = '';
        $md[] = "**Version:** `{$openApi['info']['version']}`  ";
        $md[] = '**Base URL:** `/api/v1`  ';
        $md[] = "**Generated At:** `{$date}`  ";
        $md[] = '**Authority:** [`ARCHITECTURE.md`](../ARCHITECTURE.md) §2 & §10';
        $md[] = '';
        $md[] = '---';
        $md[] = '';
        $md[] = '## 1. Architectural Rules';
        $md[] = '';
        $md[] = '1. **`/api/v1/*` is the only mount point.** All legacy paths return `410 Gone`.';
        $md[] = '2. **Standardized Response Envelope:** Every response adheres strictly to the envelope structure:';
        $md[] = '   - Success: `{ "success": true, "message": "...", "data": { ... }, "meta": { ... } }`';
        $md[] = '   - Error: `{ "success": false, "message": "...", "error": { "code": "...", "details": { ... } } }`';
        $md[] = '3. **Authentication:** Bearer token via Laravel Sanctum passed in the `Authorization: Bearer <token>` header.';
        $md[] = '4. **Money Units:** All monetary values are integer minor units (kobo, 100 kobo = 1 NGN).';
        $md[] = '';
        $md[] = '---';
        $md[] = '';

        // Group routes by Tag
        $grouped = [];
        foreach ($openApi['paths'] as $path => $methods) {
            foreach ($methods as $method => $op) {
                $tag = $op['tags'][0] ?? 'General';
                $grouped[$tag][] = [
                    'path' => $path,
                    'method' => strtoupper($method),
                    'operation' => $op,
                ];
            }
        }

        $md[] = '## 2. API Endpoints Directory';
        $md[] = '';
        $md[] = '| Method | Endpoint | Tag | Summary | Auth |';
        $md[] = '|---|---|---|---|---|';

        foreach ($grouped as $tag => $operations) {
            foreach ($operations as $item) {
                $authBadge = ! empty($item['operation']['security']) ? '🔒 Bearer' : '🌐 Public';
                $md[] = sprintf(
                    '| `%s` | `%s` | **%s** | %s | %s |',
                    $item['method'],
                    $item['path'],
                    $tag,
                    $item['operation']['summary'] ?? '',
                    $authBadge
                );
            }
        }

        $md[] = '';
        $md[] = '---';
        $md[] = '';
        $md[] = '## 3. Detailed Endpoints by Resource';
        $md[] = '';

        foreach ($grouped as $tag => $operations) {
            $md[] = "### Tag: {$tag}";
            $md[] = '';

            foreach ($operations as $item) {
                $op = $item['operation'];
                $md[] = "#### `{$item['method']}` `{$item['path']}`";
                $md[] = '';
                $md[] = '**Summary:** '.($op['summary'] ?? '');
                $md[] = '';
                $md[] = '**Description:** '.($op['description'] ?? '');
                $md[] = '';

                if (! empty($op['security'])) {
                    $md[] = '**Authentication:** Required (`Authorization: Bearer <token>`)';
                    $md[] = '';
                }

                if (! empty($op['parameters'])) {
                    $md[] = '**Parameters:**';
                    $md[] = '';
                    $md[] = '| Name | In | Required | Type | Description |';
                    $md[] = '|---|---|---|---|---|';
                    foreach ($op['parameters'] as $param) {
                        $type = $param['schema']['type'] ?? 'string';
                        $req = ! empty($param['required']) ? 'Yes' : 'No';
                        $md[] = sprintf(
                            '| `%s` | `%s` | %s | `%s` | %s |',
                            $param['name'],
                            $param['in'],
                            $req,
                            $type,
                            $param['description'] ?? ''
                        );
                    }
                    $md[] = '';
                }

                if (! empty($op['requestBody']['content']['application/json']['schema'])) {
                    $reqSchema = $op['requestBody']['content']['application/json']['schema'];
                    $schemaRef = $reqSchema['$ref'] ?? null;
                    $md[] = '**Request Body:** '.($schemaRef ? '[`'.basename($schemaRef).'`](#componentschemas'.strtolower(basename($schemaRef)).')' : '`application/json`');
                    $md[] = '';
                }

                $md[] = '**Expected Responses:**';
                $md[] = '';
                foreach ($op['responses'] as $code => $resp) {
                    $md[] = "- **`{$code}`**: ".($resp['description'] ?? '');
                }
                $md[] = '';
                $md[] = '---';
                $md[] = '';
            }
        }

        $md[] = '## 4. Component Schemas';
        $md[] = '';
        $md[] = 'The authoritative schemas defined in `components.schemas`:';
        $md[] = '';

        foreach ($openApi['components']['schemas'] as $schemaName => $schema) {
            $md[] = "### `{$schemaName}`";
            $md[] = '';
            if (! empty($schema['properties'])) {
                $md[] = '| Property | Type | Required | Description / Example |';
                $md[] = '|---|---|---|---|';
                $requiredFields = $schema['required'] ?? [];
                foreach ($schema['properties'] as $propName => $propDef) {
                    $type = $propDef['type'] ?? 'mixed';
                    $isReq = in_array($propName, $requiredFields, true) ? 'Yes' : 'No';
                    $example = isset($propDef['example']) ? 'Example: `'.json_encode($propDef['example']).'`' : ($propDef['description'] ?? '');
                    $md[] = sprintf('| `%s` | `%s` | %s | %s |', $propName, $type, $isReq, $example);
                }
                $md[] = '';
            }
        }

        return implode("\n", $md)."\n";
    }
}
