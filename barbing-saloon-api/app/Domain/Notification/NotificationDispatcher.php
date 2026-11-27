<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\Notification\Channels\EmailNotificationChannel;
use App\Domain\Notification\Channels\InAppNotificationChannel;
use App\Domain\Notification\Channels\PushNotificationChannel;
use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Services\SalonClock;
use App\Jobs\SendExpoPush;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\DeviceToken;
use App\Models\FeedbackSubmission;
use App\Models\NoShowRecord;
use App\Models\Notification;
use App\Models\NotificationBroadcast;
use App\Models\NotificationDelivery;
use App\Models\RefundRequest;
use App\Models\ServiceCategoryRequest;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Central notification dispatcher — creates canonical in-app notification records
 * and coordinates channel deliveries (In-App DB, Expo Push, Resend Email, SSE stream)
 * for all key business domain events across Customer, Barber, and Admin roles.
 */
class NotificationDispatcher
{
    /**
     * Resolve valid customer user ID from appointment relation or client email fallback.
     */
    protected static function resolveCustomerId(Appointment $appointment): ?int
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
    protected static function resolveBarberUserId(Appointment $appointment): ?int
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

    /**
     * Check if user preferences allow notifications of this type.
     */
    public static function userWantsNotification(int|User $user, string $type): bool
    {
        if (! ($user instanceof User)) {
            $user = User::find($user);
        }

        if (! $user) {
            return false;
        }

        $prefs = $user->notification_preferences ?? [];

        // Critical transaction/security types always bypass opt-outs
        if (in_array($type, ['payment_failed', 'security_alert', 'password_changed', 'account_created'], true)) {
            return true;
        }

        if (str_starts_with($type, 'booking_') || str_starts_with($type, 'appointment_')) {
            return (bool) ($prefs['notify_bookings'] ?? $prefs['notify_appointments'] ?? true);
        }

        if (str_starts_with($type, 'blog_')) {
            return (bool) ($prefs['notify_blog'] ?? true);
        }

        if (str_starts_with($type, 'wishlist_')) {
            return (bool) ($prefs['notify_wishlist'] ?? true);
        }

        if (in_array($type, ['promotion', 'promotional', 'discount'], true)) {
            return (bool) ($prefs['notify_promotions'] ?? true);
        }

        if (str_starts_with($type, 'system_')) {
            return (bool) ($prefs['notify_system'] ?? true);
        }

        return (bool) ($prefs['notify_general'] ?? true);
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

        // 1. Notify customer (In-app + Push; official email receipt is sent upon payment or pre-paid creation)
        if ($customerId) {
            $title = $appointment->deposit_paid ? 'Booking Confirmed ✂️' : 'Booking Reserved ✂️';
            $message = $appointment->deposit_paid
                ? "Your appointment for {$serviceName} with {$barberName} on ".SalonClock::formatDate($appointment->appointment_date).' at '.SalonClock::formatTime($appointment->appointment_time)." is confirmed. Booking Code: {$appointment->verification_code}"
                : "Your reservation for {$serviceName} with {$barberName} on ".SalonClock::formatDate($appointment->appointment_date).' at '.SalonClock::formatTime($appointment->appointment_time)." has been created. Ref: {$appointment->booking_reference}";

            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_created',
                title: $title,
                message: $message,
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'verification_code' => $appointment->verification_code,
                    'service_name' => $serviceName,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'normal',
                idempotencyKey: hash('sha256', "booking_created_customer:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 2. Notify assigned barber (In-app + Push)
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_created',
                title: 'New Booking Assigned 📋',
                message: "{$customerName} booked {$serviceName} for {$appointment->appointment_date} at {$appointment->appointment_time}. Ref: {$appointment->booking_reference}",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'customer_name' => $customerName,
                    'service_name' => $serviceName,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                ],
                actionUrl: '/barber/appointments',
                priority: 'normal',
                idempotencyKey: hash('sha256', "booking_created_barber:{$appointment->id}:{$barberUserId}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            data: [
                'booking_reference' => $appointment->booking_reference,
                'customer_name' => $customerName,
                'barber_name' => $barberName,
            ],
            actionUrl: '/admin/appointments',
            idempotencyKey: hash('sha256', "booking_created_admin:{$appointment->id}")
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

        // 1. Notify customer (In-app + Push + Email)
        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_approved',
                title: 'Booking Approved ✅',
                message: "Your appointment for {$serviceName} on {$appointment->appointment_date} at {$appointment->appointment_time} has been approved. See you there!",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'normal',
                idempotencyKey: hash('sha256', "booking_approved_customer:{$appointment->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        // 2. Confirmation record for assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_approved',
                title: 'Booking Approved ✅',
                message: "Appointment {$appointment->booking_reference} for {$customerName} ({$serviceName}) on {$appointment->appointment_date} at {$appointment->appointment_time} has been approved.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "booking_approved_barber:{$appointment->id}:{$barberUserId}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            idempotencyKey: hash('sha256', "booking_approved_admin:{$appointment->id}")
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

        // 1. Customer record (In-app + Push + Email)
        if ($customerId) {
            $customerMsg = $cancellerIsCustomer
                ? "You cancelled your {$serviceName} appointment ({$appointment->booking_reference}). Reason: {$reason}"
                : "Your appointment for {$serviceName} ({$appointment->booking_reference}) has been cancelled by {$cancellerName}. Reason: {$reason}";

            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_cancelled',
                title: 'Booking Cancelled ❌',
                message: $customerMsg,
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                senderId: $cancelledBy->id,
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'reason' => $reason,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'normal',
                idempotencyKey: hash('sha256', "booking_cancelled_customer:{$appointment->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        // 2. Barber record
        if ($barberUserId) {
            $barberMsg = $cancellerIsBarber
                ? "You cancelled appointment {$appointment->booking_reference} for {$customerName}. Reason: {$reason}"
                : "Appointment {$appointment->booking_reference} for {$customerName} ({$serviceName}) was cancelled by {$cancellerName}. Reason: {$reason}";

            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_cancelled',
                title: 'Booking Cancelled ❌',
                message: $barberMsg,
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                senderId: $cancelledBy->id,
                category: 'booking',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "booking_cancelled_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            idempotencyKey: hash('sha256', "booking_cancelled_admin:{$appointment->id}")
        );
    }

    /**
     * Booking completed — notify customer, barber summary, and admins.
     */
    public static function bookingCompleted(Appointment $appointment, ?User $actor = null): ?Notification
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);
        $customerNotification = null;

        // 1. Notify customer (In-app + Push; session completion email handled by SendBookingThankYou)
        if ($customerId) {
            $customerNotification = static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_completed',
                title: 'Session Complete 💈',
                message: "Your {$serviceName} session ({$appointment->verification_code}) is complete! Leave a review to share your experience.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'verification_code' => $appointment->verification_code,
                    'barber_name' => $barberName,
                ],
                actionUrl: "/customer/dashboard/reviews?appointment_id={$appointment->id}",
                priority: 'normal',
                idempotencyKey: hash('sha256', "booking_completed_customer:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_completed',
                title: 'Session Completed 💈',
                message: "Appointment {$appointment->booking_reference} for {$customerName} ({$serviceName}) marked complete.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "booking_completed_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            actionUrl: '/admin/appointments',
            idempotencyKey: hash('sha256', "booking_completed_admin:{$appointment->id}")
        );

        return $customerNotification;
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
            $forfeitNote = $appointment->deposit_forfeited
                ? ' Your paid deposit has been forfeited.'
                : '';

            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_no_show',
                title: 'Missed Appointment ⚠️',
                message: "You were marked as a no-show for your {$serviceName} appointment ({$appointment->booking_reference}).{$forfeitNote} If you had an emergency, you may submit an appeal from your bookings page.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'deposit_forfeited' => (bool) $appointment->deposit_forfeited,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'urgent',
                idempotencyKey: hash('sha256', "booking_no_show_customer:{$appointment->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_no_show',
                title: 'No-Show Recorded ⚠️',
                message: "Appointment {$appointment->booking_reference} for {$customerName} was recorded as a no-show. Your time slot is now open.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "booking_no_show_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            actionUrl: '/admin/no-shows',
            idempotencyKey: hash('sha256', "booking_no_show_admin:{$appointment->id}")
        );
    }

    /**
     * Pre-no-show urgent arrival warning — notifies customer in website/app (in_app + push).
     * Branded warning email is dispatched by SendNoShowWarningEmail using the returned notification ID.
     */
    public static function noShowWarning(Appointment $appointment, int $minutesRemaining = 5, bool $canExtend = true): ?Notification
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'your barber';
        $customerId = static::resolveCustomerId($appointment);

        if (! $customerId) {
            return null;
        }

        $extensionCount = (int) ($appointment->grace_extension_count ?? 0);
        $extendHint = $canExtend
            ? ' Running late? Request a one-time grace extension in the app/website now.'
            : ' Please check in immediately.';

        return static::dispatchNotification(
            recipientId: $customerId,
            type: 'booking_no_show_warning',
            title: "Arrival Warning: {$minutesRemaining}m Left ⚠️",
            message: "Your {$serviceName} session with {$barberName} ({$appointment->booking_reference}) will be marked as a no-show in {$minutesRemaining} minute(s).{$extendHint}",
            relatedEntityId: $appointment->id,
            recipientType: 'customer',
            category: 'booking',
            data: [
                'booking_reference' => $appointment->booking_reference,
                'minutes_remaining' => $minutesRemaining,
                'can_extend' => $canExtend,
            ],
            actionUrl: '/customer/dashboard/bookings',
            priority: 'urgent',
            idempotencyKey: hash('sha256', "no_show_warning:{$appointment->id}:ext{$extensionCount}"),
            channels: ['in_app', 'push']
        );
    }

    /**
     * Deposit explicitly forfeited after a no-show — notify customer and admins.
     */
    public static function noShowDepositForfeited(Appointment $appointment, float $amountNaira, ?string $reason = null): void
    {
        $appointment->loadMissing(['customer']);
        $customerId = static::resolveCustomerId($appointment);
        $amountFormatted = number_format($amountNaira, 2);

        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_no_show',
                title: 'Deposit Forfeited ⚠️',
                message: "Your deposit of ₦{$amountFormatted} for missed booking {$appointment->booking_reference} has been marked as forfeited per our no-show policy.".($reason ? " Note: {$reason}" : ''),
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'amount' => $amountFormatted,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'urgent',
                idempotencyKey: hash('sha256', "no_show_deposit_forfeited_cust:{$appointment->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        static::createBroadcast(
            type: 'booking_no_show',
            title: 'No-Show Deposit Forfeited 💰',
            message: "Deposit of ₦{$amountFormatted} was marked forfeited for booking {$appointment->booking_reference}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'payment',
            actionUrl: '/admin/no-shows',
            idempotencyKey: hash('sha256', "no_show_deposit_forfeited_admin:{$appointment->id}")
        );
    }

    /**
     * Customer reached the configured no-show threshold — deposit now required.
     */
    public static function noShowThresholdReached(User $customer, int $count, int $threshold): void
    {
        static::dispatchNotification(
            recipientId: $customer->id,
            type: 'booking_no_show',
            title: 'Deposit Required for Future Bookings ⚠️',
            message: "You have recorded {$count} missed appointment(s) (policy threshold: {$threshold}). Future CandyCutz bookings will require an upfront deposit.",
            recipientType: 'customer',
            category: 'booking',
            data: [
                'no_show_count' => $count,
                'threshold' => $threshold,
            ],
            actionUrl: '/customer/dashboard/bookings',
            priority: 'urgent',
            idempotencyKey: hash('sha256', "no_show_threshold:{$customer->id}:{$count}"),
            channels: ['in_app', 'push', 'email']
        );

        static::createBroadcast(
            type: 'booking_no_show',
            title: 'Customer Reached No-Show Limit ⚠️',
            message: "{$customer->name} reached {$count}/{$threshold} no-shows and now requires an upfront deposit.",
            recipientType: 'all_admins',
            relatedEntityId: $customer->id,
            category: 'booking',
            actionUrl: '/admin/no-shows',
            idempotencyKey: hash('sha256', "no_show_threshold_admin:{$customer->id}:{$count}")
        );
    }

    /**
     * Customer requested a one-time grace period extension with a valid reason.
     */
    public static function gracePeriodRequested(Appointment $appointment, User $customer, string $reason, int $extensionMinutes): void
    {
        $appointment->loadMissing(['barber.user', 'service']);
        $customerName = $customer->real_name ?? $customer->name ?? 'Customer';
        $barberUserId = static::resolveBarberUserId($appointment);

        static::dispatchNotification(
            recipientId: $customer->id,
            type: 'booking_grace_extended',
            title: "Grace Period Extended (+{$extensionMinutes}m) ⏳",
            message: "Your arrival window for {$appointment->booking_reference} has been extended by {$extensionMinutes} minutes. Please arrive promptly.",
            relatedEntityId: $appointment->id,
            recipientType: 'customer',
            category: 'booking',
            actionUrl: '/customer/dashboard/bookings',
            priority: 'high',
            idempotencyKey: hash('sha256', "grace_extended_cust:{$appointment->id}:{$appointment->grace_extension_count}"),
            channels: ['in_app', 'push']
        );

        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_grace_extended',
                title: "Client Running Late (+{$extensionMinutes}m) ⏳",
                message: "{$customerName} extended booking {$appointment->booking_reference} by {$extensionMinutes} minutes. Reason: {$reason}",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                actionUrl: '/barber/appointments',
                priority: 'high',
                idempotencyKey: hash('sha256', "grace_extended_barber:{$appointment->id}:{$appointment->grace_extension_count}"),
                channels: ['in_app', 'push']
            );
        }

        static::createBroadcast(
            type: 'booking_grace_extended',
            title: "Grace Period Extended (+{$extensionMinutes}m) ⏳",
            message: "{$customerName} extended {$appointment->booking_reference} by {$extensionMinutes}m. Reason: {$reason}",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            senderId: $customer->id,
            category: 'booking',
            actionUrl: '/admin/appointments',
            idempotencyKey: hash('sha256', "grace_extended_admin:{$appointment->id}:{$appointment->grace_extension_count}")
        );
    }

    /**
     * Customer appealed a no-show record — broadcast to admins.
     */
    public static function noShowAppealed(NoShowRecord $record, User $customer): void
    {
        $record->loadMissing('appointment');
        $ref = $record->appointment->booking_reference ?? "#{$record->appointment_id}";

        static::createBroadcast(
            type: 'booking_no_show',
            title: 'New No-Show Appeal Submitted 📩',
            message: "{$customer->name} appealed no-show for booking {$ref}: \"{$record->appeal_reason}\"",
            recipientType: 'all_admins',
            relatedEntityId: $record->id,
            senderId: $customer->id,
            category: 'booking',
            actionUrl: '/admin/no-shows',
            priority: 'high',
            idempotencyKey: hash('sha256', "no_show_appealed_admin:{$record->id}")
        );
    }

    /**
     * Admin resolved a customer's no-show appeal — notify customer.
     */
    public static function noShowAppealResolved(NoShowRecord $record): void
    {
        if (! $record->customer_id) {
            return;
        }

        $record->loadMissing('appointment');
        $ref = $record->appointment->booking_reference ?? "#{$record->appointment_id}";
        $approved = $record->appeal_outcome === 'approved';
        $title = $approved ? 'No-Show Appeal Approved ✅' : 'No-Show Appeal Declined';
        $msg = $approved
            ? "Your appeal for booking {$ref} was approved. Your no-show record has been cleared."
            : "Your appeal for booking {$ref} was reviewed and declined.";

        if ($record->resolution_note) {
            $msg .= " Note: {$record->resolution_note}";
        }

        static::dispatchNotification(
            recipientId: (int) $record->customer_id,
            type: 'booking_no_show',
            title: $title,
            message: $msg,
            relatedEntityId: $record->appointment_id,
            recipientType: 'customer',
            category: 'booking',
            actionUrl: '/customer/dashboard/bookings',
            priority: 'high',
            idempotencyKey: hash('sha256', "no_show_appeal_resolved:{$record->id}:{$record->appeal_outcome}"),
            channels: ['in_app', 'push', 'email']
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
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_checked_in',
                title: 'Checked In 📍',
                message: "You are checked in for {$serviceName} with {$barberName}. Your barber will attend to you shortly.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                idempotencyKey: hash('sha256', "booking_checked_in_customer:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_checked_in',
                title: 'Client Checked In 📍',
                message: "{$customerName} has checked in for appointment {$appointment->booking_reference} ({$serviceName}).",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                idempotencyKey: hash('sha256', "booking_checked_in_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            idempotencyKey: hash('sha256', "booking_checked_in_admin:{$appointment->id}")
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
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_in_progress',
                title: 'Service Started ✂️',
                message: "Your {$serviceName} session with {$barberName} is now in progress.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                idempotencyKey: hash('sha256', "booking_in_progress_customer:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_in_progress',
                title: 'Session In Progress ✂️',
                message: "Appointment {$appointment->booking_reference} with {$customerName} ({$serviceName}) is now in progress.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                idempotencyKey: hash('sha256', "booking_in_progress_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
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
            category: 'booking',
            idempotencyKey: hash('sha256', "booking_in_progress_admin:{$appointment->id}")
        );
    }

    /**
     * Booking reminder — notify customer ahead of scheduled session (legacy hourly signature).
     */
    public static function bookingReminder(Appointment $appointment, int $hoursBefore = 2): void
    {
        static::bookingUpcomingReminder($appointment, max(1, $hoursBefore) * 60);
    }

    /**
     * Pre-session reminder (30 minutes before appointment) — notifies Customer (in-app/web + push),
     * Admins (in-website/in-app broadcast), and assigned Barber (in-app).
     * Customer email delivery is coordinated by SendBookingReminder to use the luxury template.
     */
    public static function bookingUpcomingReminder(Appointment $appointment, int $minutesBefore = 30): ?Notification
    {
        $appointment->loadMissing(['service', 'barber.user', 'customer']);

        $serviceName = $appointment->service->name ?? 'your appointment';
        $barberName = $appointment->barber?->user->name ?? 'your barber';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';
        $bookingCode = $appointment->verification_code;
        $formattedTime = SalonClock::formatTime($appointment->appointment_time);
        $leadLabel = $minutesBefore >= 60 && $minutesBefore % 60 === 0
            ? ($minutesBefore / 60).'h Remaining'
            : "Starts in {$minutesBefore} Minutes";

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);
        $customerNotification = null;

        if ($customerId) {
            $customerNotification = static::dispatchNotification(
                recipientId: $customerId,
                type: 'booking_reminder',
                title: "Appointment Reminder: {$leadLabel} ⏰",
                message: "Your {$serviceName} with {$barberName} begins at {$formattedTime}. Keep your Booking Code ready: {$bookingCode}",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'verification_code' => $bookingCode,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'minutes_before' => $minutesBefore,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'high',
                idempotencyKey: hash('sha256', "booking_reminder_{$minutesBefore}m_customer:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'booking_reminder',
                title: "Upcoming Session in {$minutesBefore}m ⏰",
                message: "{$customerName}'s {$serviceName} session starts at {$formattedTime}. Code: {$bookingCode}",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'verification_code' => $bookingCode,
                    'minutes_before' => $minutesBefore,
                ],
                actionUrl: '/barber/appointments',
                priority: 'high',
                idempotencyKey: hash('sha256', "booking_reminder_{$minutesBefore}m_barber:{$appointment->id}:{$barberUserId}"),
                channels: ['in_app', 'push']
            );
        }

        // Notify admins in-website / in-app only (no admin email)
        static::createBroadcast(
            type: 'booking_reminder',
            title: "Upcoming Appointment in {$minutesBefore}m ⏰",
            message: "{$customerName}'s {$serviceName} with {$barberName} starts at {$formattedTime} (Code: {$bookingCode}).",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'booking',
            data: [
                'booking_reference' => $appointment->booking_reference,
                'verification_code' => $bookingCode,
                'customer_name' => $customerName,
                'barber_name' => $barberName,
                'appointment_time' => $appointment->appointment_time,
                'minutes_before' => $minutesBefore,
            ],
            actionUrl: '/admin/appointments',
            priority: 'high',
            idempotencyKey: hash('sha256', "booking_reminder_{$minutesBefore}m_admin:{$appointment->id}"),
            channels: ['in_app', 'push']
        );

        return $customerNotification;
    }

    /**
     * Barber assigned — notify barber and customer of assignment.
     */
    public static function barberAssigned(Appointment $appointment, Barber $barber): void
    {
        $appointment->loadMissing(['service', 'customer']);

        $serviceName = $appointment->service->name ?? 'Service';
        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'Customer';
        $barberUserId = (int) $barber->user_id;
        $customerId = static::resolveCustomerId($appointment);

        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'barber_assigned',
                title: 'New Barber Assignment 💈',
                message: "You have been assigned to appointment {$appointment->booking_reference} ({$customerName} - {$serviceName}) on {$appointment->appointment_date} at {$appointment->appointment_time}.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'booking',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "barber_assigned:{$appointment->id}:{$barberUserId}"),
                channels: ['in_app', 'push']
            );
        }

        if ($customerId) {
            $barberName = $barber->user?->name ?? 'A barber';
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'barber_assigned',
                title: 'Barber Assigned 💈',
                message: "{$barberName} has been assigned to your appointment ({$appointment->booking_reference}).",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'booking',
                actionUrl: '/customer/dashboard/bookings',
                idempotencyKey: hash('sha256', "barber_assigned_cust:{$appointment->id}:{$barberUserId}"),
                channels: ['in_app', 'push']
            );
        }
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
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'payment_received',
                title: 'Payment Receipt Submitted 💳',
                message: "Your payment receipt for booking {$appointment->booking_reference} (₦{$amount}) has been received and is being verified.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                actionUrl: '/customer/dashboard/bookings',
                idempotencyKey: hash('sha256', "payment_received_cust:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'payment_received',
                title: 'Payment Receipt Uploaded 💳',
                message: "{$customerName} uploaded a payment receipt (₦{$amount}) for booking {$appointment->booking_reference}. Please verify.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'payment',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "payment_received_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'payment_received',
            title: 'New Payment Receipt 💳',
            message: "{$customerName} uploaded a ₦{$amount} payment receipt for booking {$appointment->booking_reference}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'payment',
            actionUrl: '/admin/appointments',
            idempotencyKey: hash('sha256', "payment_received_admin:{$appointment->id}")
        );
    }

    /**
     * Payment verified / confirmed — notify customer, barber, and admin.
     * Official luxury email receipt is sent via SendBookingConfirmation in HandlePaymentConfirmed.
     */
    public static function paymentVerified(Appointment $appointment): ?Notification
    {
        $appointment->loadMissing(['barber.user', 'customer', 'payment']);

        $customerName = $appointment->customer->real_name ?? $appointment->customer->name ?? $appointment->client_name ?? 'A customer';
        $barberName = $appointment->barber?->user->name ?? 'Barber';
        $pricing = app(PricingService::class);
        $amount = $pricing->formatNaira($pricing->resolvedNairaAmount($appointment, $appointment->payment));
        $bookingCode = $appointment->verification_code;

        $customerId = static::resolveCustomerId($appointment);
        $barberUserId = static::resolveBarberUserId($appointment);
        $customerNotification = null;

        // 1. Notify customer (In-app + Push; official email receipt handled by SendBookingConfirmation)
        if ($customerId) {
            $customerNotification = static::dispatchNotification(
                recipientId: $customerId,
                type: 'payment_verified',
                title: 'Payment Confirmed ✅',
                message: "Your payment of ₦{$amount} is verified! Booking Code: {$bookingCode}. Your receipt has been emailed to you.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'verification_code' => $bookingCode,
                    'payment_reference' => $appointment->payment?->reference ?: $appointment->payment?->transaction_ref,
                    'amount' => $amount,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'normal',
                idempotencyKey: hash('sha256', "payment_verified_cust:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'payment_verified',
                title: 'Payment Confirmed ✅',
                message: "Payment of ₦{$amount} for appointment {$appointment->booking_reference} ({$customerName}) has been confirmed.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'payment',
                actionUrl: '/barber/appointments',
                idempotencyKey: hash('sha256', "payment_verified_barber:{$appointment->id}"),
                channels: ['in_app', 'push']
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'payment_verified',
            title: 'Payment Confirmed ✅',
            message: "Payment of ₦{$amount} confirmed for booking {$appointment->booking_reference} ({$customerName} with {$barberName}).",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'payment',
            actionUrl: '/admin/appointments',
            idempotencyKey: hash('sha256', "payment_verified_admin:{$appointment->id}")
        );

        return $customerNotification;
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

        // 1. Notify customer (In-app + Push + Email with Urgent priority)
        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'payment_failed',
                title: 'Payment Failed ⚠️',
                message: "Payment for booking {$appointment->booking_reference} could not be processed. Reason: {$failureReason}. Please retry your payment.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: [
                    'booking_reference' => $appointment->booking_reference,
                    'reason' => $failureReason,
                ],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'urgent',
                idempotencyKey: hash('sha256', "payment_failed_cust:{$appointment->id}:".now()->toDateString()),
                channels: ['in_app', 'push', 'email']
            );
        }

        // 2. Notify assigned barber
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: $barberUserId,
                type: 'payment_failed',
                title: 'Payment Failed ⚠️',
                message: "Payment attempt for booking {$appointment->booking_reference} ({$customerName}) failed. Reason: {$failureReason}.",
                relatedEntityId: $appointment->id,
                recipientType: 'barber',
                category: 'payment',
                idempotencyKey: hash('sha256', "payment_failed_barber:{$appointment->id}:".now()->toDateString()),
                channels: ['in_app', 'push']
            );
        }

        // 3. Broadcast to admins
        static::createBroadcast(
            type: 'payment_failed',
            title: 'Payment Failed ⚠️',
            message: "Payment attempt for booking {$appointment->booking_reference} ({$customerName} with {$barberName}) failed. Reason: {$failureReason}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'payment',
            priority: 'urgent',
            idempotencyKey: hash('sha256', "payment_failed_admin:{$appointment->id}:".now()->toDateString())
        );
    }

    /**
     * Refund initiated — notify customer and admins.
     */
    public static function refundInitiated(Appointment $appointment, float $amount, ?string $reason = null): void
    {
        $amountFormatted = number_format($amount, 2);
        $customerId = static::resolveCustomerId($appointment);

        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'refund_initiated',
                title: 'Refund Initiated 💳',
                message: "A refund of ₦{$amountFormatted} for booking {$appointment->booking_reference} has been initiated and is being processed.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: ['amount' => $amountFormatted, 'reason' => $reason],
                actionUrl: '/customer/dashboard/bookings',
                priority: 'urgent',
                idempotencyKey: hash('sha256', "refund_initiated_cust:{$appointment->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        static::createBroadcast(
            type: 'refund_initiated',
            title: 'Refund Initiated 💳',
            message: "Refund of ₦{$amountFormatted} initiated for booking {$appointment->booking_reference}.".($reason ? " Reason: {$reason}" : ''),
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'payment',
            idempotencyKey: hash('sha256', "refund_initiated_admin:{$appointment->id}")
        );
    }

    /**
     * Refund completed — notify customer and admins.
     */
    public static function refundCompleted(Appointment $appointment, float $amount): void
    {
        $amountFormatted = number_format($amount, 2);
        $customerId = static::resolveCustomerId($appointment);

        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'refund_completed',
                title: 'Refund Processed ✅',
                message: "Your refund of ₦{$amountFormatted} for booking {$appointment->booking_reference} has been successfully completed.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                actionUrl: '/customer/dashboard/bookings',
                priority: 'urgent',
                idempotencyKey: hash('sha256', "refund_completed_cust:{$appointment->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        static::createBroadcast(
            type: 'refund_completed',
            title: 'Refund Completed ✅',
            message: "Refund of ₦{$amountFormatted} completed for booking {$appointment->booking_reference}.",
            recipientType: 'all_admins',
            relatedEntityId: $appointment->id,
            category: 'payment',
            idempotencyKey: hash('sha256', "refund_completed_admin:{$appointment->id}")
        );
    }

    /**
     * Compassionate or dispute refund requested — notify customer (receipt) and admins for review.
     */
    public static function refundRequested(RefundRequest $refundRequest): void
    {
        $refundRequest->loadMissing(['appointment', 'customer']);
        $appointment = $refundRequest->appointment;
        if (! $appointment) {
            return;
        }

        $amountFormatted = number_format($refundRequest->refund_amount_minor / 100, 2);
        $customerId = (int) ($refundRequest->customer_id ?: static::resolveCustomerId($appointment));
        $customerName = $refundRequest->customer->real_name ?? $refundRequest->customer->name ?? $appointment->client_name ?? 'A customer';

        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'refund_requested',
                title: 'Refund Request Received 💳',
                message: "Your refund request (₦{$amountFormatted}) for booking {$appointment->booking_reference} has been submitted for admin review.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: [
                    'refund_request_id' => $refundRequest->id,
                    'booking_reference' => $appointment->booking_reference,
                    'amount' => $amountFormatted,
                    'reason_category' => $refundRequest->reason_category,
                ],
                actionUrl: '/customer/dashboard/refunds',
                priority: 'normal',
                idempotencyKey: hash('sha256', "refund_requested_cust:{$refundRequest->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }

        static::createBroadcast(
            type: 'refund_requested',
            title: 'New Refund Request Awaiting Review 📋',
            message: "{$customerName} requested a {$refundRequest->type} refund of ₦{$amountFormatted} for {$appointment->booking_reference} ({$refundRequest->reason_category}).",
            recipientType: 'all_admins',
            relatedEntityId: $refundRequest->id,
            category: 'payment',
            actionUrl: '/admin/refunds',
            priority: 'high',
            idempotencyKey: hash('sha256', "refund_requested_admin:{$refundRequest->id}")
        );
    }

    /**
     * Refund request approved by admin — notify customer.
     */
    public static function refundApproved(RefundRequest $refundRequest): void
    {
        $refundRequest->loadMissing('appointment');
        $appointment = $refundRequest->appointment;
        if (! $appointment) {
            return;
        }

        $amountFormatted = number_format($refundRequest->refund_amount_minor / 100, 2);
        $customerId = (int) ($refundRequest->customer_id ?: static::resolveCustomerId($appointment));

        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'refund_approved',
                title: 'Refund Request Approved ✅',
                message: "Your refund request of ₦{$amountFormatted} for booking {$appointment->booking_reference} has been approved and is being processed.",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: [
                    'refund_request_id' => $refundRequest->id,
                    'booking_reference' => $appointment->booking_reference,
                    'amount' => $amountFormatted,
                    'decision_note' => $refundRequest->decision_note,
                ],
                actionUrl: '/customer/dashboard/refunds',
                priority: 'urgent',
                idempotencyKey: hash('sha256', "refund_approved_cust:{$refundRequest->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }
    }

    /**
     * Refund request declined by admin — notify customer.
     */
    public static function refundRejected(RefundRequest $refundRequest): void
    {
        $refundRequest->loadMissing('appointment');
        $appointment = $refundRequest->appointment;
        if (! $appointment) {
            return;
        }

        $customerId = (int) ($refundRequest->customer_id ?: static::resolveCustomerId($appointment));
        $reasonSuffix = $refundRequest->decision_note ? " Note: {$refundRequest->decision_note}" : '';

        if ($customerId) {
            static::dispatchNotification(
                recipientId: $customerId,
                type: 'refund_rejected',
                title: 'Refund Request Declined',
                message: "Your refund request for booking {$appointment->booking_reference} was declined.{$reasonSuffix}",
                relatedEntityId: $appointment->id,
                recipientType: 'customer',
                category: 'payment',
                data: [
                    'refund_request_id' => $refundRequest->id,
                    'booking_reference' => $appointment->booking_reference,
                    'decision_note' => $refundRequest->decision_note,
                ],
                actionUrl: '/customer/dashboard/refunds',
                priority: 'normal',
                idempotencyKey: hash('sha256', "refund_rejected_cust:{$refundRequest->id}"),
                channels: ['in_app', 'push', 'email']
            );
        }
    }

    /**
     * Background image WebP optimization finished — push real-time feedback to uploader.
     */
    public static function mediaOptimized(
        int $userId,
        string $entity,
        int $entityId,
        int $originalBytes,
        int $webpBytes,
        string $newPath,
    ): void {
        $origKb = max(1, (int) round($originalBytes / 1024));
        $webpKb = max(1, (int) round($webpBytes / 1024));
        $savedPercent = $originalBytes > 0 ? max(0, (int) round((1 - ($webpBytes / $originalBytes)) * 100)) : 0;

        static::dispatchNotification(
            recipientId: $userId,
            type: 'media_optimized',
            title: 'Image Optimized to WebP ⚡',
            message: "Your uploaded image is now WebP-ready ({$origKb}KB → {$webpKb}KB, saved {$savedPercent}%).",
            relatedEntityId: $entityId,
            recipientType: 'customer',
            category: 'system',
            data: [
                'entity' => $entity,
                'entity_id' => $entityId,
                'original_kb' => $origKb,
                'optimized_kb' => $webpKb,
                'saved_percent' => $savedPercent,
                'path' => $newPath,
            ],
            priority: 'normal',
            idempotencyKey: hash('sha256', "media_optimized:{$userId}:{$entity}:{$entityId}:{$newPath}"),
            channels: ['in_app']
        );
    }

    // ─── Account & Security Events ───────────────────────────────

    /**
     * Account created — welcome notification with guide.
     */
    public static function accountCreated(User $user): void
    {
        static::dispatchNotification(
            recipientId: $user->id,
            type: 'account_created',
            title: 'Welcome to CandyCutz! 💈',
            message: 'Your account is ready. Discover our luxury barbing services, meet our master stylists, and book your next appointment seamlessly.',
            recipientType: $user->role instanceof \BackedEnum ? (string) $user->role->value : (string) $user->role,
            category: 'security',
            actionUrl: '/customer/dashboard',
            priority: 'normal',
            idempotencyKey: hash('sha256', "account_created:{$user->id}"),
            channels: ['in_app', 'push', 'email']
        );
    }

    /**
     * New user registered — notify all administrators.
     */
    public static function newUserRegisteredAdmin(User $user, string $provider = 'email'): void
    {
        $providerName = match (strtolower($provider)) {
            'google' => 'Google OAuth',
            'admin' => 'Admin Direct',
            default => 'Email Registration',
        };

        static::createBroadcast(
            type: 'user_registered_admin',
            title: 'New Client Registration 👤',
            message: "A new client, {$user->name} ({$user->email}), has registered via {$providerName}.",
            recipientType: 'all_admins',
            category: 'account',
            data: [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'provider' => $provider,
            ],
            actionUrl: '/admin/customers',
            idempotencyKey: hash('sha256', "user_registered_admin:{$user->id}"),
            channels: ['in_app', 'push']
        );
    }

    /**
     * Password changed — mandatory security notification.
     */
    public static function passwordChanged(User $user): void
    {
        static::dispatchNotification(
            recipientId: $user->id,
            type: 'password_changed',
            title: 'Security Alert: Password Changed 🔐',
            message: 'The password for your CandyCutz account was just updated. If you did not make this change, please contact support or reset your password immediately.',
            recipientType: $user->role instanceof \BackedEnum ? (string) $user->role->value : (string) $user->role,
            category: 'security',
            actionUrl: '/account/profile',
            priority: 'urgent',
            idempotencyKey: hash('sha256', "password_changed:{$user->id}:".now()->timestamp),
            channels: ['in_app', 'push', 'email']
        );
    }

    /**
     * Security alert — send high-priority security notification.
     */
    public static function securityAlert(User $user, string $title, string $message): void
    {
        static::dispatchNotification(
            recipientId: $user->id,
            type: 'security_alert',
            title: $title,
            message: $message,
            recipientType: $user->role instanceof \BackedEnum ? (string) $user->role->value : (string) $user->role,
            category: 'security',
            priority: 'urgent',
            channels: ['in_app', 'push', 'email']
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
            category: 'announcement',
            channels: ['in_app', 'push']
        );
    }

    /**
     * Create an announcement / marketing campaign broadcast across channels.
     */
    public static function targetedBroadcast(
        string $title,
        string $message,
        string $audience = 'all',
        array $channels = ['in_app', 'push'],
        ?int $senderId = null,
        ?string $actionUrl = null
    ): Notification {
        // Track campaign in notification_broadcasts table
        try {
            NotificationBroadcast::create([
                'created_by' => $senderId,
                'title' => $title,
                'message' => $message,
                'audience' => $audience,
                'channels' => $channels,
                'status' => 'sent',
                'scheduled_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log NotificationBroadcast row: '.$e->getMessage());
        }

        $recipientType = match ($audience) {
            'customers', 'all_customers' => 'all_customers',
            'barbers', 'all_barbers' => 'all_barbers',
            'admins', 'all_admins' => 'all_admins',
            default => 'all',
        };

        return static::createBroadcast(
            type: 'announcement',
            title: $title,
            message: $message,
            recipientType: $recipientType,
            senderId: $senderId,
            category: 'announcement',
            actionUrl: $actionUrl,
            channels: $channels
        );
    }

    // ─── Review / Testimonial Events ─────────────────────────────

    /**
     * Review submitted — notify the barber and admins.
     */
    public static function reviewSubmitted(Testimonial $testimonial): void
    {
        $testimonial->loadMissing(['customer', 'barber.user', 'service']);

        $customerName = $testimonial->client_name ?: $testimonial->customer?->name ?: 'A client';
        $rating = $testimonial->rating;
        $stars = str_repeat('⭐', max(1, min(5, (int) $rating)));

        // Notify barber if assigned
        $barberUserId = $testimonial->barber?->user_id;
        if ($barberUserId) {
            static::dispatchNotification(
                recipientId: (int) $barberUserId,
                type: 'review_submitted',
                title: "New Review Received {$stars}",
                message: "{$customerName} left a {$rating}-star review: \"".Str::limit($testimonial->review ?: $testimonial->comment ?: '', 80).'"',
                relatedEntityId: $testimonial->id,
                recipientType: 'barber',
                category: 'review',
                actionUrl: '/barber/reviews',
                idempotencyKey: hash('sha256', "review_submitted_barber:{$testimonial->id}"),
                channels: ['in_app', 'push']
            );
        }

        // Notify admins for review moderation
        static::createBroadcast(
            type: 'review_submitted',
            title: 'New Review for Moderation ⭐️',
            message: "{$customerName} submitted a {$rating}-star review awaiting approval.",
            recipientType: 'all_admins',
            relatedEntityId: $testimonial->id,
            category: 'review',
            actionUrl: '/admin/testimonials',
            idempotencyKey: hash('sha256', "review_submitted_admin:{$testimonial->id}")
        );
    }

    /**
     * Review approved — notify the customer.
     */
    public static function reviewApproved(Testimonial $testimonial): void
    {
        if ($testimonial->customer_id) {
            static::dispatchNotification(
                recipientId: (int) $testimonial->customer_id,
                type: 'review_approved',
                title: 'Review Published 🎉',
                message: 'Your review has been verified and published! Thank you for your feedback.',
                relatedEntityId: $testimonial->id,
                recipientType: 'customer',
                category: 'review',
                actionUrl: '/customer/reviews',
                idempotencyKey: hash('sha256', "review_approved_cust:{$testimonial->id}"),
                channels: ['in_app', 'push']
            );
        }
    }

    // ─── Category Request Events ─────────────────────────────────

    /**
     * Category requested by barber — notify admins.
     */
    public static function categoryRequested(ServiceCategoryRequest $request): void
    {
        $requesterName = $request->requester?->name ?? 'A barber';

        static::createBroadcast(
            type: 'category_requested',
            title: 'New Service Category Request 🏷️',
            message: "{$requesterName} requested a new category: \"{$request->name}\".",
            recipientType: 'all_admins',
            relatedEntityId: $request->id,
            category: 'service',
            idempotencyKey: hash('sha256', "category_requested_admin:{$request->id}")
        );
    }

    /**
     * Category request status changed (approved or rejected) — notify the barber.
     */
    public static function categoryStatusChanged(ServiceCategoryRequest $request, string $status, ?string $reason = null): void
    {
        $isApproved = $status === 'approved';
        $title = $isApproved ? 'Category Request Approved 🎉' : 'Category Request Update ℹ️';
        $message = $isApproved
            ? "Your request for category \"{$request->name}\" has been approved and is now active."
            : "Your request for category \"{$request->name}\" was rejected.".($reason ? " Reason: {$reason}" : '');

        static::dispatchNotification(
            recipientId: (int) $request->requested_by,
            type: 'category_status_changed',
            title: $title,
            message: $message,
            relatedEntityId: $request->id,
            recipientType: 'barber',
            category: 'service',
            actionUrl: '/barber/services',
            idempotencyKey: hash('sha256', "category_status_changed:{$request->id}:{$status}"),
            channels: ['in_app', 'push']
        );
    }

    // ─── Feedback Events ─────────────────────────────────────────

    /**
     * User feedback submitted — notify admins.
     */
    public static function feedbackSubmitted(FeedbackSubmission $submission): void
    {
        $submission->loadMissing('user');
        $submitterName = $submission->user->name ?? 'A user';
        $category = ucfirst(str_replace('_', ' ', $submission->category));

        static::createBroadcast(
            type: 'feedback_submitted',
            title: "New User Feedback ({$category}) 💬",
            message: "{$submitterName} submitted feedback: \"{$submission->title}\"",
            recipientType: 'all_admins',
            relatedEntityId: $submission->id,
            category: 'announcement',
            actionUrl: '/admin/feedback',
            idempotencyKey: hash('sha256', "feedback_submitted_admin:{$submission->id}")
        );
    }

    // ─── Core Dispatch Engine ────────────────────────────────────

    /**
     * Centralized multi-channel notification dispatch method.
     * Evaluates idempotency, persists canonical Notification record,
     * and coordinates channel-specific delivery jobs independently.
     *
     * @param  array<string, mixed>|null  $data
     * @param  array<int, string>  $channels  Channels to deliver to: ['in_app', 'push', 'email']
     */
    public static function dispatchNotification(
        int $recipientId,
        string $type,
        string $title,
        string $message,
        ?int $relatedEntityId = null,
        string $recipientType = 'customer',
        ?int $senderId = null,
        ?string $category = null,
        ?array $data = null,
        ?string $actionUrl = null,
        string $priority = 'normal',
        ?string $idempotencyKey = null,
        array $channels = ['in_app', 'push', 'email']
    ): ?Notification {
        if ($recipientId <= 0) {
            Log::warning("NotificationDispatcher: skipping notification with invalid recipientId: {$recipientId}");

            return null;
        }

        // Idempotency check: prevent duplicate deliveries from retried webhooks or repeated events
        if ($idempotencyKey !== null) {
            $existing = Notification::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                Log::info("NotificationDispatcher: idempotency match for key {$idempotencyKey}; returning existing record.");

                return $existing;
            }
        }

        $recipient = User::find($recipientId);
        if (! $recipient) {
            Log::warning("NotificationDispatcher: recipient user {$recipientId} not found.");

            return null;
        }

        $resolvedCategory = $category ?? match (true) {
            str_starts_with($type, 'booking_') || str_starts_with($type, 'appointment_') || str_starts_with($type, 'barber_') => 'booking',
            str_starts_with($type, 'payment_') || str_starts_with($type, 'refund_') => 'payment',
            str_starts_with($type, 'review_') => 'review',
            str_starts_with($type, 'category_') => 'service',
            str_starts_with($type, 'promotion') => 'promotion',
            in_array($type, ['security_alert', 'password_changed', 'account_created'], true) => 'security',
            default => 'announcement',
        };

        // 1. Persist canonical in-app Notification record
        try {
            $notification = Notification::create([
                'sender_id' => $senderId,
                'recipient_type' => $recipientType,
                'recipient_id' => $recipientId,
                'type' => $type,
                'category' => $resolvedCategory,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl ?: null,
                'priority' => $priority,
                'idempotency_key' => $idempotencyKey,
                'related_entity_id' => $relatedEntityId,
                'is_read' => false,
            ]);
        } catch (QueryException $e) {
            if ($idempotencyKey !== null && ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE'))) {
                Log::info("NotificationDispatcher: concurrent idempotency catch for key {$idempotencyKey}; returning existing record.");

                return Notification::where('idempotency_key', $idempotencyKey)->first();
            }

            throw $e;
        }

        $deliveryContext = [
            'action_url' => $actionUrl,
            'priority' => $priority,
            'category' => $resolvedCategory,
            ...($data ?? []),
        ];

        // 2. Deliver via independent channels
        if (in_array('in_app', $channels, true)) {
            app(InAppNotificationChannel::class)->send($recipient, $notification, $deliveryContext);
        }

        if (in_array('push', $channels, true)) {
            app(PushNotificationChannel::class)->send($recipient, $notification, $deliveryContext);
        }

        if (in_array('email', $channels, true)) {
            app(EmailNotificationChannel::class)->send($recipient, $notification, $deliveryContext);
        }

        return $notification;
    }

    /**
     * Backwards-compatible alias for dispatchNotification.
     */
    protected static function createAndPush(
        int $recipientId,
        string $type,
        string $title,
        string $message,
        ?int $relatedEntityId = null,
        string $recipientType = 'customer',
        ?int $senderId = null,
        ?string $category = null,
        ?array $data = null,
        ?string $actionUrl = null,
        string $priority = 'normal',
        ?string $idempotencyKey = null,
        array $channels = ['in_app', 'push', 'email']
    ): ?Notification {
        return static::dispatchNotification(
            recipientId: $recipientId,
            type: $type,
            title: $title,
            message: $message,
            relatedEntityId: $relatedEntityId,
            recipientType: $recipientType,
            senderId: $senderId,
            category: $category,
            data: $data,
            actionUrl: $actionUrl,
            priority: $priority,
            idempotencyKey: $idempotencyKey,
            channels: $channels
        );
    }

    /**
     * Create a broadcast notification across role members.
     *
     * @param  array<string, mixed>|null  $data
     * @param  array<int, string>  $channels
     */
    protected static function createBroadcast(
        string $type,
        string $title,
        string $message,
        string $recipientType,
        ?int $relatedEntityId = null,
        ?int $senderId = null,
        ?string $category = null,
        ?array $data = null,
        ?string $actionUrl = null,
        string $priority = 'normal',
        ?string $idempotencyKey = null,
        array $channels = ['in_app', 'push']
    ): Notification {
        if ($idempotencyKey !== null) {
            $existing = Notification::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        $resolvedCategory = $category ?? match (true) {
            str_starts_with($type, 'booking_') => 'booking',
            str_starts_with($type, 'payment_') => 'payment',
            str_starts_with($type, 'promotion') => 'promotion',
            default => 'announcement',
        };

        try {
            $notification = Notification::create([
                'sender_id' => $senderId,
                'recipient_type' => $recipientType,
                'recipient_id' => null,
                'type' => $type,
                'category' => $resolvedCategory,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl ?: null,
                'priority' => $priority,
                'idempotency_key' => $idempotencyKey,
                'related_entity_id' => $relatedEntityId,
                'is_read' => false,
            ]);
        } catch (QueryException $e) {
            if ($idempotencyKey !== null && ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE'))) {
                return Notification::where('idempotency_key', $idempotencyKey)->first();
            }

            throw $e;
        }

        // Record authoritative in-app broadcast delivery row
        if (in_array('in_app', $channels, true)) {
            NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => 'in_app',
                'status' => 'delivered',
                'attempts' => 1,
                'delivered_at' => now(),
            ]);
        }

        // Queue push notifications to role members if push channel requested
        if (in_array('push', $channels, true)) {
            static::queuePushForRole($recipientType, $title, $message, [
                'type' => $type,
                'notification_id' => $notification->id,
                'related_entity_id' => $relatedEntityId,
                'action_url' => $actionUrl,
            ]);
        }

        return $notification;
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
                'all_barbers' => ['barber'],
                'all_customers' => ['customer'],
                'all' => ['customer', 'barber', 'admin', 'super_admin'],
                default => null,
            };

            if (! $roles) {
                return;
            }

            $userIds = User::whereIn('role', $roles)->pluck('id');
            if ($userIds->isEmpty()) {
                return;
            }

            $tokens = DeviceToken::whereIn('user_id', $userIds)
                ->where('is_revoked', false)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                return;
            }

            $delivery = NotificationDelivery::create([
                'notification_id' => $data['notification_id'] ?? null,
                'channel' => 'push',
                'status' => 'pending',
                'attempts' => 0,
            ]);

            SendExpoPush::dispatch($tokens, $title, $body, $data, $delivery->id);
        } catch (\Throwable $e) {
            Log::warning("Failed to queue push notification for role {$recipientType}: ".$e->getMessage());
        }
    }
}
