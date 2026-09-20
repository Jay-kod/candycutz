<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Jobs\SendExpoPush;
use App\Models\Appointment;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Central notification dispatcher — creates in-app notification records
 * and queues Expo push notifications for all key business events.
 */
class NotificationDispatcher
{
    // ─── Booking Events ───────────────────────────────────────────

    /**
     * Booking created — notify customer + assigned barber.
     */
    public static function bookingCreated(Appointment $appointment): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service?->name ?? 'a service';
        $barberName = $appointment->barber?->user?->name ?? 'your barber';
        $customerName = $appointment->customer?->real_name ?? $appointment->customer?->name ?? $appointment->client_name ?? 'Customer';

        // Notify customer
        if ($appointment->customer_id) {
            static::createAndPush(
                recipientId: $appointment->customer_id,
                type: 'booking_created',
                title: 'Booking Confirmed ✂️',
                message: "Your appointment for {$serviceName} with {$barberName} on {$appointment->appointment_date} at {$appointment->appointment_time} has been booked. Ref: {$appointment->booking_reference}",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // Notify assigned barber
        if ($appointment->barber?->user_id) {
            static::createAndPush(
                recipientId: $appointment->barber->user_id,
                type: 'booking_created',
                title: 'New Booking Assigned 📋',
                message: "{$customerName} booked {$serviceName} for {$appointment->appointment_date} at {$appointment->appointment_time}. Ref: {$appointment->booking_reference}",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }
    }

    /**
     * Booking approved by barber — notify customer.
     */
    public static function bookingApproved(Appointment $appointment): void
    {
        $appointment->loadMissing(['service']);

        if (! $appointment->customer_id) {
            return;
        }

        $serviceName = $appointment->service?->name ?? 'your appointment';

        static::createAndPush(
            recipientId: $appointment->customer_id,
            type: 'booking_approved',
            title: 'Booking Approved ✅',
            message: "Your appointment for {$serviceName} on {$appointment->appointment_date} has been approved. See you there!",
            relatedEntityId: $appointment->id,
            recipientType: 'customer',
        );
    }

    /**
     * Booking cancelled — notify the other party.
     */
    public static function bookingCancelled(Appointment $appointment, User $cancelledBy): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service?->name ?? 'your appointment';
        $reason = $appointment->cancellation_reason ?: 'No reason provided';

        $cancellerIsCustomer = $appointment->customer_id === $cancelledBy->id;

        if ($cancellerIsCustomer) {
            // Customer cancelled → notify barber
            if ($appointment->barber?->user_id) {
                $customerName = $cancelledBy->real_name ?? $cancelledBy->name;
                static::createAndPush(
                    recipientId: $appointment->barber->user_id,
                    type: 'booking_cancelled',
                    title: 'Booking Cancelled ❌',
                    message: "{$customerName} cancelled their {$serviceName} appointment ({$appointment->booking_reference}). Reason: {$reason}",
                    relatedEntityId: $appointment->id,
                    recipientType: 'barber',
                );
            }
        } else {
            // Barber/admin cancelled → notify customer
            if ($appointment->customer_id) {
                static::createAndPush(
                    recipientId: $appointment->customer_id,
                    type: 'booking_cancelled',
                    title: 'Booking Cancelled ❌',
                    message: "Your appointment for {$serviceName} ({$appointment->booking_reference}) has been cancelled. Reason: {$reason}",
                    relatedEntityId: $appointment->id,
                    recipientType: 'customer',
                );
            }
        }
    }

    /**
     * Booking completed — notify customer.
     */
    public static function bookingCompleted(Appointment $appointment): void
    {
        if (! $appointment->customer_id) {
            return;
        }

        $appointment->loadMissing(['service']);
        $serviceName = $appointment->service?->name ?? 'your appointment';

        static::createAndPush(
            recipientId: $appointment->customer_id,
            type: 'booking_completed',
            title: 'Session Complete 💈',
            message: "Your {$serviceName} session is complete! We hope you enjoyed it. Leave a review to share your experience.",
            relatedEntityId: $appointment->id,
            recipientType: 'customer',
        );
    }

    /**
     * No-show marked — notify customer.
     */
    public static function bookingNoShow(Appointment $appointment): void
    {
        if (! $appointment->customer_id) {
            return;
        }

        $appointment->loadMissing(['service']);
        $serviceName = $appointment->service?->name ?? 'your appointment';

        static::createAndPush(
            recipientId: $appointment->customer_id,
            type: 'booking_no_show',
            title: 'Missed Appointment ⚠️',
            message: "You were marked as a no-show for your {$serviceName} appointment ({$appointment->booking_reference}). Please reschedule or contact us.",
            relatedEntityId: $appointment->id,
            recipientType: 'customer',
        );
    }

    // ─── Payment Events ──────────────────────────────────────────

    /**
     * Payment receipt uploaded — notify barber + admin.
     */
    public static function paymentReceived(Appointment $appointment): void
    {
        $appointment->loadMissing(['barber.user', 'customer']);

        $customerName = $appointment->customer?->real_name ?? $appointment->customer?->name ?? 'A customer';
        $amount = number_format((float) $appointment->grand_total, 2);

        // Notify assigned barber
        if ($appointment->barber?->user_id) {
            static::createAndPush(
                recipientId: $appointment->barber->user_id,
                type: 'payment_received',
                title: 'Payment Receipt Uploaded 💳',
                message: "{$customerName} uploaded a payment receipt (₦{$amount}) for booking {$appointment->booking_reference}. Please verify.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // Broadcast to admins
        static::createBroadcast(
            type: 'payment_received',
            title: 'New Payment Receipt 💳',
            message: "{$customerName} uploaded a ₦{$amount} payment receipt for booking {$appointment->booking_reference}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
        );
    }

    /**
     * Payment verified — notify customer.
     */
    public static function paymentVerified(Appointment $appointment): void
    {
        if (! $appointment->customer_id) {
            return;
        }

        $amount = number_format((float) $appointment->grand_total, 2);

        static::createAndPush(
            recipientId: $appointment->customer_id,
            type: 'payment_verified',
            title: 'Payment Confirmed ✅',
            message: "Your payment of ₦{$amount} for booking {$appointment->booking_reference} has been verified. Thank you!",
            relatedEntityId: $appointment->id,
            recipientType: 'customer',
        );
    }

    // ─── System / Admin Events ───────────────────────────────────

    /**
     * Broadcast a system-wide notification to a role group.
     */
    public static function systemBroadcast(string $title, string $message, string $recipientType = 'all', ?int $senderId = null): Notification
    {
        return static::createBroadcast(
            type: 'system_update',
            title: $title,
            message: $message,
            recipientType: $recipientType,
            senderId: $senderId,
        );
    }

    // ─── Internals ───────────────────────────────────────────────

    /**
     * Create a targeted notification for a specific user and optionally push.
     */
    protected static function createAndPush(
        int $recipientId,
        string $type,
        string $title,
        string $message,
        ?int $relatedEntityId = null,
        string $recipientType = 'customer',
        ?int $senderId = null,
    ): Notification {
        $notification = Notification::create([
            'sender_id' => $senderId,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_entity_id' => $relatedEntityId,
            'is_read' => false,
        ]);

        // Queue push notification to all user's registered devices
        static::queuePush($recipientId, $title, $message, [
            'type' => $type,
            'notification_id' => $notification->id,
            'related_entity_id' => $relatedEntityId,
        ]);

        return $notification;
    }

    /**
     * Create a broadcast notification (no specific recipient_id, uses recipient_type).
     */
    protected static function createBroadcast(
        string $type,
        string $title,
        string $message,
        string $recipientType,
        ?int $relatedEntityId = null,
        ?int $senderId = null,
    ): Notification {
        return Notification::create([
            'sender_id' => $senderId,
            'recipient_type' => $recipientType,
            'recipient_id' => null,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_entity_id' => $relatedEntityId,
            'is_read' => false,
        ]);
    }

    /**
     * Dispatch push notifications to all registered devices for a given user.
     */
    protected static function queuePush(int $userId, string $title, string $body, array $data = []): void
    {
        try {
            $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->toArray();

            if (empty($tokens)) {
                return;
            }

            SendExpoPush::dispatch($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::warning("Failed to queue push notification for user {$userId}: ".$e->getMessage());
        }
    }
}
