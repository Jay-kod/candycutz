<?php
/**
 * Centralized in-app notifications for CandyCutz.
 * Every mutating action should notify affected users and relay to the admin dashboard.
 */

function cc_ensure_notifications_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $marker = sys_get_temp_dir() . '/candycutz_notif_schema.marker';
    if (file_exists($marker)) {
        $done = true;
        return;
    }

    try {
        $pdo->exec("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'system'");
    } catch (PDOException $e) {
        // Table may not exist yet or column already updated.
    }

    @file_put_contents($marker, time());
    $done = true;
}

function cc_notify(PDO $pdo, array $opts): ?int
{
    cc_ensure_notifications_schema($pdo);

    $senderId = $opts['sender_id'] ?? null;
    $recipientType = $opts['recipient_type'];
    $recipientId = $opts['recipient_id'] ?? null;
    $type = $opts['type'] ?? 'system';
    $title = $opts['title'] ?? 'Notification';
    $message = $opts['message'] ?? '';
    $relatedEntityId = $opts['related_entity_id'] ?? null;

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $senderId,
            $recipientType,
            $recipientId,
            $type,
            $title,
            $message,
            $relatedEntityId,
        ]);
        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('cc_notify failed: ' . $e->getMessage());
        return null;
    }
}

function cc_notify_admin(PDO $pdo, array $opts): ?int
{
    $opts['recipient_type'] = 'admin';
    $opts['recipient_id'] = null;
    return cc_notify($pdo, $opts);
}

function cc_notify_customer(PDO $pdo, int $customerUserId, array $opts): ?int
{
    $opts['recipient_type'] = 'customer';
    $opts['recipient_id'] = $customerUserId;
    return cc_notify($pdo, $opts);
}

function cc_notify_barber_user(PDO $pdo, int $barberUserId, array $opts): ?int
{
    $opts['recipient_type'] = 'barber';
    $opts['recipient_id'] = $barberUserId;
    return cc_notify($pdo, $opts);
}

function cc_barber_user_id(PDO $pdo, int $barberId): ?int
{
    $stmt = $pdo->prepare('SELECT user_id FROM barbers WHERE id = ?');
    $stmt->execute([$barberId]);
    $id = $stmt->fetchColumn();
    return $id ? (int) $id : null;
}

function cc_notify_barber(PDO $pdo, int $barberId, array $opts): ?int
{
    $barberUserId = cc_barber_user_id($pdo, $barberId);
    if (!$barberUserId) {
        return null;
    }
    return cc_notify_barber_user($pdo, $barberUserId, $opts);
}

function cc_notify_all_customers(PDO $pdo, array $opts): ?int
{
    $opts['recipient_type'] = 'all_customers';
    $opts['recipient_id'] = null;
    return cc_notify($pdo, $opts);
}

/**
 * Notify customer, assigned barber, and admin dashboard for appointment-related events.
 */
function cc_notify_appointment_parties(PDO $pdo, int $appointmentId, array $opts): void
{
    $stmt = $pdo->prepare('SELECT customer_id, barber_id FROM appointments WHERE id = ?');
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$appointment) {
        return;
    }

    $senderId = $opts['sender_id'] ?? null;
    $type = $opts['type'] ?? 'booking';
    $relatedEntityId = $appointmentId;

    if (!empty($opts['customer']) && $appointment['customer_id']) {
        cc_notify_customer($pdo, (int) $appointment['customer_id'], array_merge($opts['customer'], [
            'sender_id' => $senderId,
            'type' => $type,
            'related_entity_id' => $relatedEntityId,
        ]));
    }

    if (!empty($opts['barber']) && $appointment['barber_id']) {
        cc_notify_barber($pdo, (int) $appointment['barber_id'], array_merge($opts['barber'], [
            'sender_id' => $senderId,
            'type' => $type,
            'related_entity_id' => $relatedEntityId,
        ]));
    }

    if (!empty($opts['admin'])) {
        cc_notify_admin($pdo, array_merge($opts['admin'], [
            'sender_id' => $senderId,
            'type' => $type,
            'related_entity_id' => $relatedEntityId,
        ]));
    }
}

function cc_notifications_for_user_query(): string
{
    return 'SELECT * FROM notifications WHERE
        (recipient_id = ?)
        OR (recipient_type = \'all_customers\' AND ? = \'customer\')
        OR (recipient_type = \'barber\' AND recipient_id = ? AND ? = \'barber\')
        OR (recipient_type = \'admin\' AND ? IN (\'admin\', \'super_admin\'))
        ORDER BY created_at DESC LIMIT 200';
}

function cc_fetch_notifications_for_user(PDO $pdo, int $userId, string $role): array
{
    $stmt = $pdo->prepare(cc_notifications_for_user_query());
    $stmt->execute([$userId, $role, $userId, $role, $role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cc_mark_all_notifications_read(PDO $pdo, int $userId, string $role): void
{
    $stmt = $pdo->prepare(
        'UPDATE notifications SET is_read = 1 WHERE
            recipient_id = ?
            OR (recipient_type = \'all_customers\' AND ? = \'customer\')
            OR (recipient_type = \'barber\' AND recipient_id = ? AND ? = \'barber\')
            OR (recipient_type = \'admin\' AND ? IN (\'admin\', \'super_admin\'))'
    );
    $stmt->execute([$userId, $role, $userId, $role, $role]);
}
