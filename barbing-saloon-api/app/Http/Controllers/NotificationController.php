<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController
{
    /**
     * Whitelist of event types permitted for general public / store broadcast.
     * Every other notification type across the entire platform is strictly private
     * and requires direct recipient matching (recipient_id === $user->id).
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
     * Helper to safely extract role as a string
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
     * Enforces privacy-by-default across ALL notifications in the entire system:
     * - Every customer and barber receives ONLY direct personal notifications
     *   targeted explicitly to their user ID (recipient_id === $user->id).
     * - Broadcast notifications (recipient_id IS NULL) are ONLY visible if the
     *   notification is explicitly typed as a public announcement / system broadcast.
     * - Operational shop alerts (recipient_type IN ('all_admins', 'admin')) are
     *   strictly restricted to administrators.
     */
    private function getScopedQuery(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $role = $this->getUserRoleString($user);
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        return Notification::query()
            ->where(function ($q) use ($user, $role, $isAdmin) {
                // 1. Direct personal notifications explicitly targeted to this user ID
                // (Applies to all notification types across the entire system: bookings, payments,
                // reviews, accounts, passwords, security, receipts, walk-ins, refunds, etc.)
                $q->where('recipient_id', $user->id);

                // 2. Broadcast notifications (recipient_id IS NULL)
                if ($isAdmin) {
                    // Admins see operational shop broadcasts and system announcements
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('recipient_id')
                            ->whereIn('recipient_type', ['all', 'all_admins', 'admin']);
                    });
                } elseif ($role === 'barber') {
                    // Barbers ONLY see explicit staff broadcasts or general announcements
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('recipient_id')
                            ->whereIn('recipient_type', ['all', 'all_barbers'])
                            ->whereIn('type', self::BARBER_BROADCAST_TYPES);
                    });
                } else {
                    // Customers ONLY see explicit public store announcements / system updates
                    // NEVER any user-specific or operational notification
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('recipient_id')
                            ->whereIn('recipient_type', ['all', 'all_customers'])
                            ->whereIn('type', self::CUSTOMER_BROADCAST_TYPES);
                    });
                }
            });
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $query = $this->getScopedQuery($user);

        // Optional filter by notification type (e.g. 'booking', 'payment', 'system_update')
        if ($request->has('type') && $request->filled('type') && $request->query('type') !== 'all') {
            $type = (string) $request->query('type');
            if ($type === 'booking') {
                $query->where(function ($q) {
                    $q->where('type', 'booking')
                        ->orWhere('type', 'like', 'booking_%');
                });
            } elseif ($type === 'payment') {
                $query->where(function ($q) {
                    $q->where('type', 'payment')
                        ->orWhere('type', 'like', 'payment_%');
                });
            } else {
                $query->where('type', $type);
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get unread notification count (lightweight for badge polling)
     */
    public function unreadCount(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $count = $this->getScopedQuery($user)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    /**
     * Create a notification (used by barbers, admins, or internal events)
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
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

        // Authorization: Only admins can create broadcast notifications
        if ($recipientId === null && ! in_array($role, ['admin', 'super_admin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send broadcast notifications',
            ], 403);
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

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'data' => $notification,
        ], 201);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(int|string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notification = $this->getScopedQuery($user)
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read for the user
     */
    public function markAllRead(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $this->getScopedQuery($user)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a specific notification
     */
    public function destroy(int|string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notification = $this->getScopedQuery($user)
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Get user notification preferences
     */
    public function getNotificationSettings(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $user->notification_preferences ?? [
                'notify_bookings' => true,
                'notify_system' => true,
                'notify_wishlist' => true,
                'notify_blog' => true,
                'notify_general' => true,
            ],
        ]);
    }

    /**
     * Update user notification preferences
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
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

        $user->update([
            'notification_preferences' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences saved successfully',
            'data' => $user->refresh()->notification_preferences,
        ]);
    }

    /**
     * Register or update a device token for push notifications
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'nullable|string|in:ios,android,web',
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'platform' => $validated['platform'] ?? 'android',
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully',
            'data' => $deviceToken,
        ]);
    }

    /**
     * Delete a device token
     */
    public function deleteDeviceToken(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }
}
