-- Schema sync patch: aligns tables created by create_tables.sql with the
-- columns/tables the API code expects. Every ALTER is conditional so the
-- script is safe to run against both a fresh volume and an existing DB.

USE candycutz_db;

-- ============================================================
-- USERS: username, last_username_change, last_email_change
-- ============================================================
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username');
SET @sql = IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `name`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_username_change');
SET @sql = IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `last_username_change` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_email_change');
SET @sql = IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `last_email_change` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'notification_preferences');
SET @sql = IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `notification_preferences` JSON NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- BARBERS: bank details (for barber/team payout info)
-- ============================================================
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'barbers' AND COLUMN_NAME = 'bank_name');
SET @sql = IF(@col = 0, 'ALTER TABLE `barbers` ADD COLUMN `bank_name` VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'barbers' AND COLUMN_NAME = 'account_name');
SET @sql = IF(@col = 0, 'ALTER TABLE `barbers` ADD COLUMN `account_name` VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'barbers' AND COLUMN_NAME = 'account_number');
SET @sql = IF(@col = 0, 'ALTER TABLE `barbers` ADD COLUMN `account_number` VARCHAR(50) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- APPOINTMENTS: booking_type, verification_code
-- ============================================================
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'booking_type');
SET @sql = IF(@col = 0, 'ALTER TABLE `appointments` ADD COLUMN `booking_type` ENUM(''online'', ''walk_in'') NOT NULL DEFAULT ''online'' AFTER `status`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'verification_code');
SET @sql = IF(@col = 0, 'ALTER TABLE `appointments` ADD COLUMN `verification_code` VARCHAR(64) NULL DEFAULT NULL AFTER `booking_type`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- PAYMENTS: receipt_image, verified_by, user_id, nullable customer, enum status
-- ============================================================
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'receipt_image');
SET @sql = IF(@col = 0, 'ALTER TABLE `payments` ADD COLUMN `receipt_image` VARCHAR(255) NULL AFTER `transaction_ref`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'verified_by');
SET @sql = IF(@col = 0, 'ALTER TABLE `payments` ADD COLUMN `verified_by` VARCHAR(20) NULL DEFAULT NULL AFTER `receipt_image`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'user_id');
SET @sql = IF(@col = 0, 'ALTER TABLE `payments` ADD COLUMN `user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `customer_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE `payments` MODIFY COLUMN `customer_id` BIGINT UNSIGNED NULL;
ALTER TABLE `payments` MODIFY COLUMN `status` ENUM('pending', 'awaiting_verification', 'successful', 'failed', 'refunded') DEFAULT 'pending';

-- ============================================================
-- TESTIMONIALS: service_id
-- ============================================================
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'testimonials' AND COLUMN_NAME = 'service_id');
SET @sql = IF(@col = 0, 'ALTER TABLE `testimonials` ADD COLUMN `service_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `barber_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- SETTINGS: group column (optional grouping metadata)
-- ============================================================
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'group');
SET @sql = IF(@col = 0, 'ALTER TABLE `settings` ADD COLUMN `group` VARCHAR(50) NULL DEFAULT NULL AFTER `value`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- BLOG_REACTIONS table (loves / dislikes per customer per post)
-- ============================================================
CREATE TABLE IF NOT EXISTS `blog_reactions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `reaction_type` ENUM('love', 'dislike') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `blog_reactions_post_customer` (`post_id`, `customer_id`),
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MESSAGES table (contact form submissions)
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sender_name` VARCHAR(255) NOT NULL,
    `sender_email` VARCHAR(255) NOT NULL,
    `sender_phone` VARCHAR(50) NULL,
    `message` TEXT NOT NULL,
    `recipient_email` VARCHAR(255) NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS table (kept consistent with cc_ensure_notifications_schema)
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sender_id` BIGINT UNSIGNED NULL,
    `recipient_type` ENUM('all_customers', 'customer', 'barber', 'admin') NOT NULL,
    `recipient_id` BIGINT UNSIGNED NULL,
    `type` ENUM('booking', 'payment', 'system', 'alert') NOT NULL DEFAULT 'system',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `related_entity_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `notifications_recipient_index` (`recipient_type`, `recipient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;