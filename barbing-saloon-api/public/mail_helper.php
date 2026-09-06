<?php

function cc_ensure_mail_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS `mail_outbox` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `recipient_email` VARCHAR(255) NOT NULL,
        `subject` VARCHAR(255) NOT NULL,
        `body_html` LONGTEXT,
        `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
        `error_message` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `sent_at` TIMESTAMP NULL DEFAULT NULL,
        INDEX `mail_outbox_recipient_index` (`recipient_email`),
        INDEX `mail_outbox_status_index` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL,
        `token_hash` VARCHAR(64) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `used_at` DATETIME NULL DEFAULT NULL,
        INDEX `password_resets_email_index` (`email`),
        INDEX `password_resets_token_index` (`token_hash`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $skipUsersAuthColumns = ['auth_provider', 'provider_id'];
    foreach ($skipUsersAuthColumns as $column) {
        $colCount = (int)$pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = " . $pdo->quote($column)
        )->fetchColumn();
        if ($colCount === 0) {
            $def = $column === 'auth_provider' ? "VARCHAR(50) NULL DEFAULT NULL AFTER `role`" : "VARCHAR(120) NULL DEFAULT NULL AFTER `auth_provider`";
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `" . $column . "` " . $def);
        }
    }
}

function cc_setting(PDO $pdo, string $key, $default = null)
{
    $envMap = [
        'mail_host' => 'MAIL_HOST',
        'mail_port' => 'MAIL_PORT',
        'mail_username' => 'MAIL_USERNAME',
        'mail_password' => 'MAIL_PASSWORD',
        'mail_from' => 'MAIL_FROM',
        'mail_from_name' => 'MAIL_FROM_NAME',
        'app_url' => 'APP_URL',
        'google_client_id' => 'GOOGLE_CLIENT_ID',
        'apple_client_id' => 'APPLE_CLIENT_ID',
        'apple_team_id' => 'APPLE_TEAM_ID',
        'apple_key_id' => 'APPLE_KEY_ID',
    ];
    if (isset($envMap[$key])) {
        $env = getenv($envMap[$key]);
        if ($env !== false && $env !== '') {
            return $env;
        }
    }
    $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return ($value === false || $value === null) ? $default : $value;
}

function cc_mail_config(PDO $pdo): array
{
    $host = cc_setting($pdo, 'mail_host', 'smtp-relay.brevo.com');
    $port = (int) cc_setting($pdo, 'mail_port', '587');
    if ($port <= 0) $port = 587;
    return [
        'host' => $host,
        'port' => $port,
        'username' => (string) cc_setting($pdo, 'mail_username', ''),
        'password' => (string) cc_setting($pdo, 'mail_password', ''),
        'from' => (string) cc_setting($pdo, 'mail_from', 'no-reply@candycutz.app'),
        'from_name' => (string) cc_setting($pdo, 'mail_from_name', 'CandyCutz'),
    ];
}

function cc_mail_is_configured(array $cfg): bool
{
    if ($cfg['host'] === '' || $cfg['from'] === '') {
        return false;
    }
    if (strpos($cfg['host'], 'localhost') !== false || $cfg['host'] === '127.0.0.1') {
        return true;
    }
    return $cfg['username'] !== '';
}

function cc_smtp_send(array $cfg, string $to, string $subject, string $html): array
{
    $host = $cfg['host'];
    $port = $cfg['port'];
    $timeout = 20;
    $errno = 0;
    $errstr = '';
    $prefix = $port === 465 ? 'ssl://' : '';

    $conn = @stream_socket_client($prefix . $host . ':' . $port, $errno, $errstr, $timeout);
    if (!$conn) {
        return ['ok' => false, 'error' => "Connection failed: $errstr ($errno)"];
    }
    stream_set_timeout($conn, $timeout);

    $readResponse = function () use ($conn) {
        $lines = [];
        while (($line = fgets($conn, 515)) !== false) {
            $lines[] = $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return implode('', $lines);
    };

    $send = function ($cmd) use ($conn, $readResponse) {
        fwrite($conn, $cmd . "\r\n");
        return $readResponse();
    };

    $lastError = '';

    $resp = $readResponse();
    if (!preg_match('/^220/', $resp)) {
        fclose($conn);
        return ['ok' => false, 'error' => 'SMTP greeting failed: ' . trim($resp)];
    }

    $resp = $send('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    if (!preg_match('/^250/', $resp)) {
        fclose($conn);
        return ['ok' => false, 'error' => 'EHLO failed: ' . trim($resp)];
    }

    if ($port !== 465 && stripos($resp, 'STARTTLS') !== false) {
        $resp = $send('STARTTLS');
        if (preg_match('/^220/', $resp)) {
            $crypto = stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$crypto) {
                fclose($conn);
                return ['ok' => false, 'error' => 'STARTTLS negotiation failed'];
            }
            $resp = $send('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
            if (!preg_match('/^250/', $resp)) {
                fclose($conn);
                return ['ok' => false, 'error' => 'EHLO (TLS) failed: ' . trim($resp)];
            }
        }
    }

    if (!empty($cfg['username'])) {
        $resp = $send('AUTH LOGIN');
        if (preg_match('/^334/', $resp)) {
            fwrite($conn, base64_encode($cfg['username']) . "\r\n");
            $resp = $readResponse();
            if (preg_match('/^334/', $resp)) {
                fwrite($conn, base64_encode($cfg['password']) . "\r\n");
                $resp = $readResponse();
                if (!preg_match('/^235/', $resp)) {
                    fclose($conn);
                    return ['ok' => false, 'error' => 'Authentication failed: ' . trim($resp)];
                }
            } else {
                fclose($conn);
                return ['ok' => false, 'error' => 'AUTH LOGIN failed: ' . trim($resp)];
            }
        }
    }

    $from = $cfg['from'];
    $resp = $send('MAIL FROM:<' . $from . '>');
    if (!preg_match('/^250/', $resp)) {
        fclose($conn);
        return ['ok' => false, 'error' => 'MAIL FROM failed: ' . trim($resp)];
    }

    $resp = $send('RCPT TO:<' . $to . '>');
    if (!preg_match('/^250/', $resp)) {
        fclose($conn);
        return ['ok' => false, 'error' => 'RCPT TO failed: ' . trim($resp)];
    }

    $resp = $send('DATA');
    if (!preg_match('/^354/', $resp)) {
        fclose($conn);
        return ['ok' => false, 'error' => 'DATA failed: ' . trim($resp)];
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = "From: " . $cfg['from_name'] . " <" . $from . ">\r\n"
        . "To: <" . $to . ">\r\n"
        . "Subject: " . $encodedSubject . "\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "X-Mailer: CandyCutz/1.0\r\n";

    $body = str_replace("\n.", "\n..", $html);
    fwrite($conn, $headers . "\r\n" . $body . "\r\n.\r\n");
    $resp = $readResponse();

    if (!preg_match('/^250/', $resp)) {
        $lastError = trim($resp);
    }

    $send('QUIT');
    @fclose($conn);

    if ($lastError !== '') {
        return ['ok' => false, 'error' => $lastError];
    }
    return ['ok' => true, 'error' => ''];
}

function cc_record_outbox(PDO $pdo, string $to, string $subject, string $html, bool $ok, string $error = ''): void
{
    $stmt = $pdo->prepare("INSERT INTO mail_outbox (recipient_email, subject, body_html, status, error_message, sent_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $to,
        $subject,
        $html,
        $ok ? 'sent' : 'failed',
        $error !== '' ? $error : null,
        $ok ? date('Y-m-d H:i:s') : null,
    ]);
}

function cc_email_wrap(string $innerHtml): string
{
    return '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#0b0d12;font-family:Arial,Helvetica,sans-serif;">'
        . '<div style="max-width:600px;margin:0 auto;background:#11141c;border:1px solid #262a35;border-radius:14px;overflow:hidden;">'
        . '<div style="background:#0b0d12;padding:28px 36px;text-align:center;border-bottom:1px solid #262a35;">'
        . '<span style="font-size:22px;font-weight:800;letter-spacing:3px;color:#d4af37;text-transform:uppercase;">Candy<span style="color:#ffffff;">Cutz</span></span>'
        . '</div>'
        . '<div style="padding:36px;color:#e6e9f0;font-size:15px;line-height:1.7;">' . $innerHtml . '</div>'
        . '<div style="background:#0b0d12;padding:20px 36px;text-align:center;border-top:1px solid #262a35;color:#71778a;font-size:12px;">'
        . '&copy; ' . date('Y') . ' CandyCutz Barbershop. All rights reserved.<br/>Premium grooming with a sharper standard.'
        . '</div>'
        . '</div></body></html>';
}

function cc_send_email(PDO $pdo, string $to, string $subject, string $plainBody): array
{
    $cfg = cc_mail_config($pdo);
    if (!cc_mail_is_configured($cfg)) {
        $html = cc_email_wrap(nl2br(cc_e($plainBody)));
        cc_record_outbox($pdo, $to, $subject, $html, false, 'SMTP not configured');
        return ['ok' => false, 'error' => 'Mail transport not configured'];
    }

    $html = cc_email_wrap(nl2br(cc_e($plainBody)));
    try {
        $result = cc_smtp_send($cfg, $to, $subject, $html);
    } catch (Throwable $e) {
        $result = ['ok' => false, 'error' => $e->getMessage()];
    }

    cc_record_outbox($pdo, $to, $subject, $html, $result['ok'], $result['error']);
    return $result;
}

function cc_e(?string $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function cc_send_welcome_email(PDO $pdo, string $to, string $name): void
{
    cc_send_email($pdo, $to, 'Welcome to CandyCutz!', "Hi {$name},\n\nWelcome to the CandyCutz family! Your account has been created successfully.\n\nYou can now book appointments, manage your wishlist, and enjoy premium grooming services.\n\nWe can't wait to see you in the chair!\n\n- The CandyCutz Team");
}

function cc_send_login_alert_email(PDO $pdo, string $to, string $name): void
{
    cc_send_email($pdo, $to, 'New sign-in to your CandyCutz account', "Hi {$name},\n\nWe noticed a successful sign-in to your CandyCutz account.\n\nIf this was you, no further action is needed. If you did not sign in, please reset your password immediately.\n\n- The CandyCutz Team");
}

function cc_send_password_reset_email(PDO $pdo, string $to, string $name, string $resetLink): void
{
    $html = cc_email_wrap(
        "Hi {$name},<br/><br/>We received a request to reset your CandyCutz password.<br/><br/>"
        . '<a href="' . e($resetLink) . '" style="display:inline-block;background:#d4af37;color:#0b0d12;text-decoration:none;font-weight:700;padding:14px 28px;border-radius:8px;">Reset my password</a>'
        . "<br/><br/>This link is valid for 30 minutes. If you didn't request this, you can safely ignore this email.<br/><br/>- The CandyCutz Team"
    );
    $cfg = cc_mail_config($pdo);
    if (cc_mail_is_configured($cfg)) {
        $result = cc_smtp_send($cfg, $to, 'Reset your CandyCutz password', $html);
        cc_record_outbox($pdo, $to, 'Reset your CandyCutz password', $html, $result['ok'], $result['error']);
        return;
    }
    cc_record_outbox($pdo, $to, 'Reset your CandyCutz password', $html, false, 'Mail transport not configured');
}

function cc_send_password_changed_email(PDO $pdo, string $to, string $name): void
{
    cc_send_email($pdo, $to, 'Your CandyCutz password was changed', "Hi {$name},\n\nYour CandyCutz account password was successfully changed.\n\nIf you did not make this change, please contact support immediately.\n\n- The CandyCutz Team");
}