<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Jobs\SendExpoPush;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Central notification dispatcher — creates in-app notification records
 * and queues Expo push notifications for all key business events across
 * the core triad: Actor / Barber, Customer, and Admin.
 */
class NotificationDispatcher
{
    /**
     * Resolve valid customer user ID from appointment relation or client email fallback.
     */
    private static function resolveCustomerId(Appointment $appointment): ?int
    {
        if ($appointment->customer_id) {
            return (int) $appointment->customer_id;
        }

        if (! empty($appointment->client_email)) {
            $userId = User::where('email', $appointment->client_email)->value('id');
            if ($userId) {
                return (int) $userId;
            }
        }

        return null;
    }

    /**
     * Resolve valid barber user ID from barber relation or barber_id model lookup.
     */
    private static function resolveBarberUserId(Appointment $appointment): ?int
    {
        if ($appointment->barber?->user_id) {
            return (int) $appointment->barber->user_id;
        }

        if ($appointment->barber_id) {
            $userId = Barber::where('id', $appointment->barber_id)->value('user_id');
            if ($userId) {
                return (int) $userId;
            }
        }

        return null;
    }

    // ─── Booking Events ───────────────────────────────────────────

    /**
     * Booking created — notify customer, assigned barber, and admins.
     */
    public static function bookingCreated(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'a service';
        $barberName = $appointment->barber?->user->name ?? 'your barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_created',
                title: 'Booking Confirmed ✂️',
                message: "Your appointment for {$serviceName} with {$barberName} on {$appointment->appointment_date} at {$appointment->appointment_time} has been booked. Ref: {$appointment->booking_reference}",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_created',
                title: 'New Booking Assigned 📋',
                message: "{$customerName} booked {$serviceName} for {$appointment->appointment_date} at {$appointment->appointment_time}. Ref: {$appointment->booking_reference}",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to branch & system admins
        static::createBroadcast(
            type: 'booking_created',
            title: 'New Booking Created 📋',
            message: "{$customerName} booked {$serviceName} with {$barberName} for {$appointment->appointment_date} at {$appointment->appointment_time}. Ref: {$appointment->booking_reference}",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $actor?->id,
        );
    }

    /**
     * Booking approved / confirmed — notify customer, barber confirmation, and admins.
     */
    public static function bookingApproved(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_approved',
                title: 'Booking Approved ✅',
                message: "Your appointment for {$serviceName} on {$appointment->appointment_date} at {$appointment->appointment_time} has been approved. See you there!",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Confirmation record for assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_approved',
                title: 'Booking Approved ✅',
                message: "Appointment {$appointment->booking_reference} for {$customerName} ({$serviceName}) on {$appointment->appointment_date} at {$appointment->appointment_time} has been approved.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'booking_approved',
            title: 'Booking Approved ✅',
            message: "Appointment {$appointment->booking_reference} ({$customerName} with {$barberName}) was approved for {$appointment->appointment_date}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $actor?->id,
        );
    }

    /**
     * Booking cancelled — notify customer, barber, and admins.
     */
    public static function bookingCancelled(Appointment $appointment, User $cancelledBy): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';
        $reason = $appointment->cancellation_reason ?: 'No reason provided';
        $cancellerName = $cancelledBy->real_name ?? $cancelledBy->name ?? 'User';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        $cancellerIsCustomer = $customerId === $cancelledBy->id;
        $cancellerIsBarber = $barberUserId === $cancelledBy->id;

        // 1. Customer record
        if ($customerId) {
            $customerMsg = $cancellerIsCustomer
                ? "You cancelled your {$serviceName} appointment ({$appointment->booking_reference}). Reason: {$reason}"
                : "Your appointment for {$serviceName} ({$appointment->booking_reference}) has been cancelled by {$cancellerName}. Reason: {$reason}";

            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_cancelled',
                title: 'Booking Cancelled ❌',
                message: $customerMsg,
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                senderId: $cancelledBy->id,
            );
        }

        // 2. Barber record
        if ($barberUserId) {
            $barberMsg = $cancellerIsBarber
                ? "You cancelled appointment {$appointment->booking_reference} for {$customerName}. Reason: {$reason}"
                : "Appointment {$appointment->booking_reference} for {$customerName} ({$serviceName}) was cancelled by {$cancellerName}. Reason: {$reason}";

            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_cancelled',
                title: 'Booking Cancelled ❌',
                message: $barberMsg,
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                senderId: $cancelledBy->id,
            );
        }

        // 3. Admin broadcast
        static::createBroadcast(
            type: 'booking_cancelled',
            title: 'Booking Cancelled ❌',
            message: "Appointment {$appointment->booking_reference} ({$customerName} with {$barberName}) was cancelled by {$cancellerName}. Reason: {$reason}",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $cancelledBy->id,
        );
    }

    /**
     * Booking completed — notify customer, barber summary, and admins.
     */
    public static function bookingCompleted(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_completed',
                title: 'Session Complete 💈',
                message: "Your {$serviceName} session is complete! We hope you enjoyed it. Leave a review to share your experience.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_completed',
                title: 'Session Completed 💈',
                message: "Appointment {$appointment->booking_reference} for {$customerName} ({$serviceName}) marked complete.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'booking_completed',
            title: 'Booking Completed 💈',
            message: "Appointment {$appointment->booking_reference} ({$customerName} with {$barberName}) was marked completed.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $actor?->id,
        );
    }

    /**
     * No-show marked — notify customer, barber, and admins.
     */
    public static function bookingNoShow(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_no_show',
                title: 'Missed Appointment ⚠️',
                message: "You were marked as a no-show for your {$serviceName} appointment ({$appointment->booking_reference}). Please reschedule or contact us.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_no_show',
                title: 'No-Show Recorded ⚠️',
                message: "Appointment {$appointment->booking_reference} for {$customerName} was recorded as a no-show.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'booking_no_show',
            title: 'No-Show Recorded ⚠️',
            message: "Appointment {$appointment->booking_reference} ({$customerName} with {$barberName}) was recorded as a no-show.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $actor?->id,
        );
    }

    /**
     * Client checked in — notify customer, barber, and admins.
     */
    public static function bookingCheckedIn(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'your barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_checked_in',
                title: 'Checked In 📍',
                message: "You are checked in for {$serviceName} with {$barberName}. Your barber will attend to you shortly.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_checked_in',
                title: 'Client Checked In 📍',
                message: "{$customerName} has checked in for appointment {$appointment->booking_reference} ({$serviceName}).",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'booking_checked_in',
            title: 'Client Checked In 📍',
            message: "{$customerName} checked in for appointment {$appointment->booking_reference} with {$barberName}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $actor?->id,
        );
    }

    /**
     * Service in progress — notify customer, barber, and admins.
     */
    public static function bookingInProgress(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'your barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'booking_in_progress',
                title: 'Service Started ✂️',
                message: "Your {$serviceName} session with {$barberName} is now in progress.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'booking_in_progress',
                title: 'Session In Progress ✂️',
                message: "Appointment {$appointment->booking_reference} with {$customerName} ({$serviceName}) is now in progress.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'booking_in_progress',
            title: 'Service In Progress ✂️',
            message: "Appointment {$appointment->booking_reference} ({$customerName} with {$barberName}) is now in progress.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $actor?->id,
        );
    }

    // ─── Payment Events ──────────────────────────────────────────

    /**
     * Payment receipt uploaded — notify customer, barber, and admin.
     */
    public static function paymentReceived(Appointment $appointment): void
    {
        $appointment->loadMissing(['barber.user', 'customer']);

        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'A customer';
        $amount = number_format((float) $appointment->grand_total, 2);

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'payment_received',
                title: 'Payment Receipt Submitted 💳',
                message: "Your payment receipt for booking {$appointment->booking_reference} (₦{$amount}) has been received and is being verified.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'payment_received',
                title: 'Payment Receipt Uploaded 💳',
                message: "{$customerName} uploaded a payment receipt (₦{$amount}) for booking {$appointment->booking_reference}. Please verify.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'payment_received',
            title: 'New Payment Receipt 💳',
            message: "{$customerName} uploaded a ₦{$amount} payment receipt for booking {$appointment->booking_reference}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
        );
    }

    /**
     * Payment verified / confirmed — notify customer, barber, and admin.
     */
    public static function paymentVerified(Appointment $appointment): void
    {
        $appointment->loadMissing(['barber.user', 'customer']);

        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'A customer';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $amount = number_format((float) $appointment->grand_total, 2);

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'payment_verified',
                title: 'Payment Confirmed ✅',
                message: "Your payment of ₦{$amount} for booking {$appointment->booking_reference} has been verified. Thank you!",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'payment_verified',
                title: 'Payment Confirmed ✅',
                message: "Payment of ₦{$amount} for appointment {$appointment->booking_reference} ({$customerName}) has been confirmed.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'payment_verified',
            title: 'Payment Confirmed ✅',
            message: "Payment of ₦{$amount} confirmed for booking {$appointment->booking_reference} ({$customerName} with {$barberName}).",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
        );
    }

    /**
     * Payment failed — notify customer, barber, and admin.
     */
    public static function paymentFailed(Appointment $appointment, ?string $reason = null): void
    {
        $appointment->loadMissing(['barber.user', 'customer']);

        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $failureReason = $reason ?: 'The transaction could not be completed';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);

        // 1. Notify customer
        if ($customerId) {
            static::createAndPush(
                recipientId: $customerId,
                type: 'payment_failed',
                title: 'Payment Failed ⚠️',
                message: "Payment for booking {$appointment->booking_reference} could not be processed. Reason: {$failureReason}. Please retry your payment.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::createAndPush(
                recipientId: $barberUserId,
                type: 'payment_failed',
                title: 'Payment Failed ⚠️',
                message: "Payment attempt for booking {$appointment->booking_reference} ({$customerName}) failed. Reason: {$failureReason}.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'payment_failed',
            title: 'Payment Failed ⚠️',
            message: "Payment attempt for booking {$appointment->booking_reference} ({$customerName} with {$barberName}) failed. Reason: {$failureReason}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
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
    ): ?Notification {
        if ($recipientId <= 0) {
            Log::warning("NotificationDispatcher: skipping notification with invalid recipientId: {$recipientId}");
            return null;
        }

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
     * Create a broadcast notification and dispatch push notifications to role members.
     */
    protected static function createBroadcast(
        string $type,
        string $title,
        string $message,
        string $recipientType,
        ?int $relatedEntityId = null,
        ?int $senderId = null,
    ): Notification {
        $notification = Notification::create([
            'sender_id' => $senderId,
            'recipient_type' => $recipientType,
            'recipient_id' => null,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_entity_id' => $relatedEntityId,
            'is_read' => false,
        ]);

        // Queue push notifications to all users matching this role
        static::queuePushForRole($recipientType, $title, $message, [
            'type' => $type,
            'notification_id' => $notification->id,
            'related_entity_id' => $relatedEntityId,
        ]);

        return $notification;
    }

    /**
     * Dispatch push notifications to all registered devices for a given user.
     *
     * @param  array<string, mixed>  $data
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

    /**
     * Dispatch push notifications to all users of a given role.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function queuePushForRole(string $recipientType, string $title, string $body, array $data = []): void
    {
        try {
            $roles = match ($recipientType) {
                'all_admins', 'admin' => ['admin', 'super_admin'],
                'all_barbers', 'barber' => ['barber'],
                'all_customers', 'customer' => ['customer'],
                default => null,
            };

            if (! $roles) {
                return;
            }

            $userIds = User::whereIn('role', $roles)->pluck('id');
            if ($userIds->isEmpty()) {
                return;
            }

            $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
            if (empty($tokens)) {
                return;
            }

            SendExpoPush::dispatch($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::warning("Failed to queue push notification for role {$recipientType}: ".$e->getMessage());
        }
    }
}
