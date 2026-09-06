<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController
{
    /**
     * Helper to safely extract role as a string
     */
    private function getUserRoleString(User $user): string
    {
        if ($user->role instanceof \BackedEnum) {
            return (string) $user->role->value;
        }

        return (string) ($user->role ?? 'customer');
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $role = $this->getUserRoleString($user);

        $broadcastTypes = [
            'all',
            'all_' . $role,
            'all_' . $role . 's',
            $role,
        ];

        // Specific aliases for common pluralizations
        if ($role === 'customer') {
            $broadcastTypes[] = 'all_customers';
        } elseif ($role === 'barber') {
            $broadcastTypes[] = 'all_barbers';
        } elseif ($role === 'admin' || $role === 'super_admin') {
            $broadcastTypes[] = 'all_admins';
            $broadcastTypes[] = 'admin';
        }

        $query = Notification::query()
            ->where(function ($q) use ($user, $broadcastTypes) {
                $q->where('recipient_id', $user->id)
                  ->orWhere(function ($sub) use ($broadcastTypes) {
                      $sub->whereIn('recipient_type', $broadcastTypes)
                          ->whereNull('recipient_id');
                  });
            });

        // Optional filter by notification type (e.g. 'booking', 'payment', 'system_update')
        if ($request->has('type') && $request->filled('type') && $request->query('type') !== 'all') {
            $query->where('type', $request->query('type'));
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Create a notification (used by barbers, admins, or internal events)
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:64',
            'recipient_type' => 'nullable|string|max:64',
            'recipient_id' => 'nullable|integer',
            'related_entity_id' => 'nullable|integer',
        ]);

        $notification = Notification::create([
            'sender_id' => $user->id,
            'recipient_type' => $validated['recipient_type'] ?? 'all_customers',
            'recipient_id' => $validated['recipient_id'] ?? null,
            'type' => $validated['type'] ?? 'system_update',
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
            'data' => $notification
        ], 201);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($id): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $role = $this->getUserRoleString($user);
        $broadcastTypes = ['all', 'all_' . $role, 'all_' . $role . 's', $role];

        $notification = Notification::where('id', $id)
            ->where(function ($query) use ($user, $broadcastTypes) {
                $query->where('recipient_id', $user->id)
                      ->orWhereIn('recipient_type', $broadcastTypes);
            })->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read for the user
     */
    public function markAllRead(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        Notification::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a specific notification
     */
    public function destroy($id): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $role = $this->getUserRoleString($user);
        $broadcastTypes = ['all', 'all_' . $role, 'all_' . $role . 's', $role];

        $notification = Notification::where('id', $id)
            ->where(function ($query) use ($user, $broadcastTypes) {
                $query->where('recipient_id', $user->id)
                      ->orWhereIn('recipient_type', $broadcastTypes);
            })->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Get user notification preferences
     */
    public function getNotificationSettings(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $user->notification_preferences ?? [
                'notify_bookings' => true,
                'notify_system' => true,
                'notify_wishlist' => true,
                'notify_blog' => true,
                'notify_general' => true
            ]
        ]);
    }

    /**
     * Update user notification preferences
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $prefs = $request->all();
        $user->update([
            'notification_preferences' => $prefs
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences saved successfully',
            'data' => $user->refresh()->notification_preferences
        ]);
    }
}
