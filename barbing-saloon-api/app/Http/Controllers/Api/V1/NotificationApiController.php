<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Notification\NotificationDispatcher;
use App\Http\Resources\NotificationResource;
use App\Http\Responses\ApiResponse;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationBroadcast;
use App\Models\NotificationDelivery;
use App\Models\NotificationRead;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationApiController
{
    /**
     * Whitelist of event types permitted for general public / store broadcast.
     */
    public const CUSTOMER_BROADCAST_TYPES = [
        'system_update',
        'system_announcement',
        'announcement',
        'broadcast',
        'promotion',
        'promotional',
    ];

    /**
     * Whitelist of event types permitted for general staff broadcast.
     */
    public const BARBER_BROADCAST_TYPES = [
        'system_update',
        'system_announcement',
        'announcement',
        'broadcast',
        'staff_announcement',
    ];

    /**
     * Helper to safely extract role as a string.
     */
    private function getUserRoleString(User $user): string
    {
        if ($user->role instanceof \BackedEnum) {
            return (string) $user->role->value;
        }

        return is_string($user->role) ? $user->role : 'customer';
    }

    /**
     * Build an authoritative query scoped strictly to the authenticated user.
     */
    protected function getScopedQuery(User $user): Builder
    {
        $role = $this->getUserRoleString($user);
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        return Notification::query()
            ->where(function (Builder $q) use ($user, $role, $isAdmin) {
                // 1. Direct personal notifications targeted explicitly to this user
                $q->where('recipient_id', $user->id);

                // 2. Broadcast notifications (recipient_id IS NULL)
                if ($isAdmin) {
                    $q->orWhere(function (Builder $sub) {
                        $sub->whereNull('recipient_id')
                            ->whereIn('recipient_type', ['all', 'all_admins', 'admin']);
                    });
                } elseif ($role === 'barber') {
                    $q->orWhere(function (Builder $sub) {
                        $sub->whereNull('recipient_id')
                            ->whereIn('recipient_type', ['all', 'all_barbers'])
                            ->whereIn('type', self::BARBER_BROADCAST_TYPES);
                    });
                } else {
                    $q->orWhere(function (Builder $sub) {
                        $sub->whereNull('recipient_id')
                            ->whereIn('recipient_type', ['all', 'all_customers'])
                            ->whereIn('type', self::CUSTOMER_BROADCAST_TYPES);
                    });
                }
            });
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $query = $this->getScopedQuery($user)
            ->with(['reads' => fn ($q) => $q->where('user_id', $user->id)]);

        if ($request->has('type') && $request->filled('type') && $request->query('type') !== 'all') {
            $type = (string) $request->query('type');
            if ($type === 'booking') {
                $query->where(function (Builder $q) {
                    $q->where('type', 'booking')
                        ->orWhere('type', 'like', 'booking_%');
                });
            } elseif ($type === 'payment') {
                $query->where(function (Builder $q) {
                    $q->where('type', 'payment')
                        ->orWhere('type', 'like', 'payment_%');
                });
            } else {
                $query->where('type', $type);
            }
        }

        $limit = min(max((int) $request->query('limit', 150), 1), 300);
        $notifications = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        return ApiResponse::success(NotificationResource::collection($notifications), 'Notifications retrieved');
    }

    /**
     * Get unread notification count with broadcast read receipt awareness.
     */
    public function unreadCount(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        // Direct unread
        $directUnread = Notification::query()
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Broadcast unread: matches role, recipient_id IS NULL, and NOT in notification_reads for this user
        $role = $this->getUserRoleString($user);
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        $broadcastQuery = Notification::query()->whereNull('recipient_id');

        if ($isAdmin) {
            $broadcastQuery->whereIn('recipient_type', ['all', 'all_admins', 'admin']);
        } elseif ($role === 'barber') {
            $broadcastQuery->whereIn('recipient_type', ['all', 'all_barbers'])
                ->whereIn('type', self::BARBER_BROADCAST_TYPES);
        } else {
            $broadcastQuery->whereIn('recipient_type', ['all', 'all_customers'])
                ->whereIn('type', self::CUSTOMER_BROADCAST_TYPES);
        }

        $broadcastUnread = $broadcastQuery
            ->whereDoesntHave('reads', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        $totalCount = $directUnread + $broadcastUnread;

        return ApiResponse::success(['count' => $totalCount], 'Unread count retrieved');
    }

    /**
     * Server-Sent Events (SSE) stream for real-time notification delivery.
     * Supports Last-Event-ID header / query parameter for reliable reconnection cursors.
     */
    public function stream(Request $request): StreamedResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $userId = $user->id;
        $rawCursor = $request->header('Last-Event-ID') ?? $request->query('last_event_id');
        $parsedCursor = is_scalar($rawCursor)
            ? filter_var((string) $rawCursor, FILTER_VALIDATE_INT)
            : false;
        $initialCursor = $parsedCursor !== false && $parsedCursor >= 0 ? $parsedCursor : null;
        // The PHP built-in server is single-worker, so it cannot hold an SSE connection open.
        $singlePoll = PHP_SAPI === 'cli-server' || app()->runningUnitTests();

        $response = new StreamedResponse(function () use ($userId, $initialCursor, $singlePoll) {
            $user = User::find($userId);
            if (! $user) {
                return;
            }

            if (ob_get_level() > 0 && ! $singlePoll) {
                ob_end_clean();
            }

            $lastId = $initialCursor ?? ((int) (Notification::max('id') ?? 0));
            @set_time_limit(35);
            $startTime = time();

            while ($singlePoll || time() - $startTime < 25) {
                if (connection_aborted()) {
                    break;
                }

                $query = $this->getScopedQuery($user);
                if ($lastId > 0) {
                    $query->where('id', '>', $lastId);
                }

                $newNotifications = $query->orderBy('id', 'asc')->get();

                if ($newNotifications->isNotEmpty()) {
                    $lastId = (int) $newNotifications->last()->id;

                    echo "id: {$lastId}\n";
                    echo "event: notification\n";
                    echo 'data: '.json_encode(NotificationResource::collection($newNotifications)->resolve())."\n\n";
                    flush();
                } elseif ($singlePoll) {
                    echo "id: {$lastId}\n";
                }

                echo ": ping\n\n";
                flush();

                if ($singlePoll) {
                    break;
                }

                sleep(2);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Create a notification.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $role = $this->getUserRoleString($user);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:64',
            'recipient_type' => 'nullable|string|max:64',
            'recipient_id' => 'nullable|integer',
            'related_entity_id' => 'nullable|integer',
        ]);

        $recipientId = $validated['recipient_id'] ?? null;
        $recipientType = $validated['recipient_type'] ?? 'all_customers';

        if ($recipientId === null && ! in_array($role, ['admin', 'super_admin'], true)) {
            return ApiResponse::error('Unauthorized to send broadcast notifications', [], 403, 'UNAUTHORIZED');
        }

        $type = $validated['type'] ?? 'system_update';
        if ($recipientId === null && ! in_array($type, self::CUSTOMER_BROADCAST_TYPES, true)) {
            $type = 'system_update';
        }

        $notification = Notification::create([
            'sender_id' => $user->id,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'type' => $type,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'related_entity_id' => $validated['related_entity_id'] ?? null,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::success(new NotificationResource($notification), 'Notification created successfully', 201);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(int|string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $notification = $this->getScopedQuery($user)
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return ApiResponse::error('Notification not found', [], 404, 'NOT_FOUND');
        }

        if ($notification->recipient_id !== null) {
            $notification->update(['is_read' => true]);
        } else {
            NotificationRead::firstOrCreate(
                ['notification_id' => $notification->id, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }

        return ApiResponse::success(null, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read for the user.
     */
    public function markAllRead(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        DB::transaction(function () use ($user) {
            // 1. Mark direct unread notifications
            Notification::query()
                ->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // 2. Mark unread broadcast notifications
            $role = $this->getUserRoleString($user);
            $isAdmin = in_array($role, ['admin', 'super_admin'], true);

            $broadcastQuery = Notification::query()->whereNull('recipient_id');

            if ($isAdmin) {
                $broadcastQuery->whereIn('recipient_type', ['all', 'all_admins', 'admin']);
            } elseif ($role === 'barber') {
                $broadcastQuery->whereIn('recipient_type', ['all', 'all_barbers'])
                    ->whereIn('type', self::BARBER_BROADCAST_TYPES);
            } else {
                $broadcastQuery->whereIn('recipient_type', ['all', 'all_customers'])
                    ->whereIn('type', self::CUSTOMER_BROADCAST_TYPES);
            }

            $unreadBroadcastIds = $broadcastQuery
                ->whereDoesntHave('reads', function (Builder $q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->pluck('id');

            $records = [];
            $now = now();
            foreach ($unreadBroadcastIds as $notifId) {
                $records[] = [
                    'notification_id' => $notifId,
                    'user_id' => $user->id,
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($records)) {
                NotificationRead::insertOrIgnore($records);
            }
        });

        return ApiResponse::success(null, 'All notifications marked as read');
    }

    /**
     * Delete a notification.
     */
    public function destroy(int|string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $notification = $this->getScopedQuery($user)
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return ApiResponse::error('Notification not found', [], 404, 'NOT_FOUND');
        }

        $role = $this->getUserRoleString($user);
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        if ($notification->recipient_id !== null) {
            $notification->delete();
        } else {
            // Broadcast notification: Admins can fully delete.
            // Regular users dismiss it by recording a read receipt so others keep it.
            if ($isAdmin) {
                $notification->delete();
            } else {
                NotificationRead::firstOrCreate(
                    ['notification_id' => $notification->id, 'user_id' => $user->id],
                    ['read_at' => now()]
                );
            }
        }

        return ApiResponse::success(null, 'Notification deleted');
    }

    /**
     * Clear all notifications for the authenticated user.
     * Admins can optionally pass ?scope=all to wipe all system notification records.
     */
    public function clearAll(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $role = $this->getUserRoleString($user);
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        if ($request->query('scope') === 'all' && $isAdmin) {
            DB::transaction(function () {
                NotificationDelivery::query()->delete();
                NotificationRead::query()->delete();
                Notification::query()->delete();
            });

            return ApiResponse::success(null, 'All system notifications cleared successfully');
        }

        DB::transaction(function () use ($user, $role, $isAdmin) {
            // Delete personal notifications
            Notification::where('recipient_id', $user->id)->delete();

            // Mark unread broadcasts as read for this user so they don't reappear
            $broadcastQuery = Notification::query()->whereNull('recipient_id');

            if ($isAdmin) {
                $broadcastQuery->whereIn('recipient_type', ['all', 'all_admins', 'admin']);
            } elseif ($role === 'barber') {
                $broadcastQuery->whereIn('recipient_type', ['all', 'all_barbers'])
                    ->whereIn('type', self::BARBER_BROADCAST_TYPES);
            } else {
                $broadcastQuery->whereIn('recipient_type', ['all', 'all_customers'])
                    ->whereIn('type', self::CUSTOMER_BROADCAST_TYPES);
            }

            $broadcastIds = $broadcastQuery->pluck('id');
            $now = now();
            $reads = [];
            foreach ($broadcastIds as $bId) {
                $reads[] = [
                    'notification_id' => $bId,
                    'user_id' => $user->id,
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (! empty($reads)) {
                NotificationRead::insertOrIgnore($reads);
            }
        });

        return ApiResponse::success(null, 'All notifications cleared successfully');
    }

    /**
     * Admin bulk clear of all system notification records and delivery logs.
     */
    public function adminClearAll(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! in_array($this->getUserRoleString($user), ['admin', 'super_admin'], true)) {
            return ApiResponse::error('Unauthorized', [], 403, 'UNAUTHORIZED');
        }

        DB::transaction(function () {
            NotificationDelivery::query()->delete();
            NotificationRead::query()->delete();
            Notification::query()->delete();
        });

        return ApiResponse::success(null, 'All system notification records cleared successfully');
    }

    /**
     * Get user notification preferences.
     */
    public function getNotificationSettings(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        return ApiResponse::success($user->notification_preferences ?? [
            'notify_bookings' => true,
            'notify_system' => true,
            'notify_wishlist' => true,
            'notify_blog' => true,
            'notify_general' => true,
        ], 'Notification settings retrieved');
    }

    /**
     * Update user notification preferences.
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $validated = $request->validate([
            'notify_appointments' => 'boolean',
            'notify_bookings' => 'boolean',
            'notify_system' => 'boolean',
            'notify_promotions' => 'boolean',
            'notify_wishlist' => 'boolean',
            'notify_blog' => 'boolean',
            'notify_general' => 'boolean',
        ]);

        $current = is_array($user->notification_preferences) ? $user->notification_preferences : [];
        $merged = array_merge($current, $validated);

        $user->update([
            'notification_preferences' => $merged,
        ]);

        return ApiResponse::success($user->refresh()->notification_preferences, 'Notification preferences saved successfully');
    }

    /**
     * Get granular category x channel notification preferences matrix.
     */
    public function getNotificationPreferences(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $categories = [
            ['key' => 'security', 'label' => 'Account & Security', 'description' => 'Password changes, login alerts, and critical security notices'],
            ['key' => 'booking', 'label' => 'Appointments & Bookings', 'description' => 'Booking confirmations, reminders, cancellations, and status updates'],
            ['key' => 'payment', 'label' => 'Payments & Receipts', 'description' => 'Payment confirmations, receipt uploads, and refund notifications'],
            ['key' => 'review', 'label' => 'Reviews & Ratings', 'description' => 'New review submissions, verifications, and approvals'],
            ['key' => 'service', 'label' => 'Services & Stylists', 'description' => 'Service updates, barber assignments, and category availability'],
            ['key' => 'announcement', 'label' => 'System Announcements', 'description' => 'Maintenance notices, platform updates, and branch news'],
            ['key' => 'promotion', 'label' => 'Promotions & Discounts', 'description' => 'Special offers, seasonal discounts, and reward campaigns'],
        ];

        $channels = [
            ['key' => 'in_app', 'label' => 'In-App'],
            ['key' => 'push', 'label' => 'Push Notifications'],
            ['key' => 'email', 'label' => 'Email'],
        ];

        $defaults = [
            'security' => ['in_app' => true, 'push' => true, 'email' => true, 'locked_channels' => ['in_app', 'email']],
            'booking' => ['in_app' => true, 'push' => true, 'email' => true, 'locked_channels' => []],
            'payment' => ['in_app' => true, 'push' => true, 'email' => true, 'locked_channels' => ['in_app', 'email']],
            'review' => ['in_app' => true, 'push' => true, 'email' => false, 'locked_channels' => []],
            'service' => ['in_app' => true, 'push' => true, 'email' => false, 'locked_channels' => []],
            'announcement' => ['in_app' => true, 'push' => true, 'email' => false, 'locked_channels' => []],
            'promotion' => ['in_app' => true, 'push' => false, 'email' => false, 'locked_channels' => []],
        ];

        $savedPrefs = UserNotificationPreference::where('user_id', $user->id)->get();

        $preferences = $defaults;
        $quietHours = [
            'enabled' => false,
            'start' => '22:00',
            'end' => '08:00',
        ];

        foreach ($savedPrefs as $pref) {
            if (isset($preferences[$pref->category][$pref->channel])) {
                if (! in_array($pref->channel, $preferences[$pref->category]['locked_channels'] ?? [], true)) {
                    $preferences[$pref->category][$pref->channel] = (bool) $pref->is_enabled;
                }
            }
            if (! empty($pref->quiet_hours_start) && ! empty($pref->quiet_hours_end)) {
                $quietHours['enabled'] = true;
                $quietHours['start'] = $pref->quiet_hours_start;
                $quietHours['end'] = $pref->quiet_hours_end;
            }
        }

        return ApiResponse::success([
            'categories' => $categories,
            'channels' => $channels,
            'preferences' => $preferences,
            'quiet_hours' => $quietHours,
        ], 'Notification preferences retrieved successfully');
    }

    /**
     * Update granular category x channel notification preferences matrix.
     */
    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'array',
            'quiet_hours' => 'nullable|array',
            'quiet_hours.enabled' => 'nullable|boolean',
            'quiet_hours.start' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
            'quiet_hours.end' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
        ]);

        $quietHoursEnabled = (bool) ($validated['quiet_hours']['enabled'] ?? false);
        $quietHoursStart = $quietHoursEnabled ? ($validated['quiet_hours']['start'] ?? '22:00') : null;
        $quietHoursEnd = $quietHoursEnabled ? ($validated['quiet_hours']['end'] ?? '08:00') : null;

        $locked = [
            'security' => ['in_app', 'email'],
            'payment' => ['in_app', 'email'],
        ];

        $validCategories = ['security', 'booking', 'payment', 'review', 'service', 'announcement', 'promotion'];
        $validChannels = ['in_app', 'push', 'email'];

        DB::transaction(function () use ($user, $validated, $locked, $validCategories, $validChannels, $quietHoursStart, $quietHoursEnd) {
            foreach ($validated['preferences'] as $category => $channels) {
                if (! in_array($category, $validCategories, true) || ! is_array($channels)) {
                    continue;
                }

                foreach ($channels as $channel => $isEnabled) {
                    if (! in_array($channel, $validChannels, true)) {
                        continue;
                    }

                    // Enforce that security and payment in-app/email cannot be disabled
                    $finalState = in_array($channel, $locked[$category] ?? [], true) ? true : (bool) $isEnabled;

                    UserNotificationPreference::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'category' => $category,
                            'channel' => $channel,
                        ],
                        [
                            'is_enabled' => $finalState,
                            'quiet_hours_start' => $quietHoursStart,
                            'quiet_hours_end' => $quietHoursEnd,
                        ]
                    );
                }
            }
        });

        return $this->getNotificationPreferences();
    }

    /**
     * Register or update a device token for push notifications.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $validated = $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'nullable|string|in:ios,android,web',
            'app_version' => 'nullable|string|max:64',
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'platform' => $validated['platform'] ?? 'android',
                'app_version' => $validated['app_version'] ?? null,
                'is_revoked' => false,
                'last_seen_at' => now(),
            ]
        );

        return ApiResponse::success($deviceToken, 'Device token registered successfully');
    }

    /**
     * Delete a device token.
     */
    public function deleteDeviceToken(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        return ApiResponse::success(null, 'Device token removed successfully');
    }

    /**
     * Admin: List channel delivery records with filtering.
     */
    public function adminDeliveries(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $query = NotificationDelivery::with(['notification'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('channel') && $request->channel !== 'all') {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = (string) $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                    ->orWhere('failure_code', 'like', "%{$search}%")
                    ->orWhere('failure_reason', 'like', "%{$search}%")
                    ->orWhereHas('notification', function (Builder $sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%")
                            ->orWhere('message', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = min(100, max(5, (int) $request->input('per_page', 25)));
        $deliveries = $query->paginate($perPage);

        return ApiResponse::success($deliveries, 'Notification deliveries retrieved successfully');
    }

    /**
     * Admin: List broadcast history.
     */
    public function adminBroadcasts(): JsonResponse
    {
        $broadcasts = NotificationBroadcast::with('creator:id,name,email')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return ApiResponse::success($broadcasts, 'Broadcasts retrieved successfully');
    }

    /**
     * Admin: Store and dispatch targeted broadcast campaign.
     */
    public function adminStoreBroadcast(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthenticated', [], 401, 'UNAUTHENTICATED');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|string|in:all,customers,barbers,admins',
            'channels' => 'required|array|min:1',
            'channels.*' => 'string|in:in_app,push,email',
            'action_url' => 'nullable|string|max:255',
        ]);

        $notification = NotificationDispatcher::targetedBroadcast(
            title: $validated['title'],
            message: $validated['message'],
            audience: $validated['audience'],
            channels: $validated['channels'],
            senderId: $user->id,
            actionUrl: $validated['action_url'] ?? null
        );

        return ApiResponse::success(new NotificationResource($notification), 'Broadcast dispatched successfully', 201);
    }

    /**
     * Admin: Aggregated analytics and delivery health statistics.
     */
    public function adminAnalytics(): JsonResponse
    {
        $today = now()->startOfDay();
        $thirtyDaysAgo = now()->subDays(30);

        $totalToday = Notification::where('created_at', '>=', $today)->count();
        $total30Days = Notification::where('created_at', '>=', $thirtyDaysAgo)->count();

        $deliveriesByChannel = NotificationDelivery::select('channel', 'status', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('channel', 'status')
            ->get();

        $devicesByPlatform = DeviceToken::select('platform', DB::raw('count(*) as count'))
            ->where('is_revoked', false)
            ->groupBy('platform')
            ->get()
            ->pluck('count', 'platform');

        $activeTokensCount = DeviceToken::where('is_revoked', false)->count();
        $activeUsersWithPush = DeviceToken::where('is_revoked', false)->distinct('user_id')->count('user_id');

        return ApiResponse::success([
            'total_notifications_today' => $totalToday,
            'total_notifications_30d' => $total30Days,
            'deliveries_by_channel' => $deliveriesByChannel,
            'devices_by_platform' => $devicesByPlatform,
            'active_push_tokens' => $activeTokensCount,
            'active_users_with_push' => $activeUsersWithPush,
        ], 'Notification analytics retrieved successfully');
    }
}
