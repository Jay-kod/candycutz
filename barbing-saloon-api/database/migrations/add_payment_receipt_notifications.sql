USE candycutz_db;

-- 1. Add receipt_image to payments
ALTER TABLE `payments` ADD COLUMN `receipt_image` VARCHAR(255) NULL AFTER `transaction_ref`;
ALTER TABLE `payments` MODIFY COLUMN `status` ENUM('pending', 'awaiting_verification', 'successful', 'failed', 'refunded') DEFAULT 'pending';

-- 2. Create notifications table
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
