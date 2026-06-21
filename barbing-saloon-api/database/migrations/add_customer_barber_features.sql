USE candycutz_db;

-- 1. Modify services table to add barber_id (nullable, if null it's a global service)
ALTER TABLE `services` ADD COLUMN `barber_id` BIGINT UNSIGNED NULL AFTER `id`;
ALTER TABLE `services` ADD CONSTRAINT `fk_services_barber` FOREIGN KEY (`barber_id`) REFERENCES `barbers`(`id`) ON DELETE CASCADE;

-- 2. Create wishlists table
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `item_type` ENUM('service', 'gallery') NOT NULL,
    `item_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `wishlists_customer_item` (`customer_id`, `item_type`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create payments table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `appointment_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'successful', 'failed', 'refunded') DEFAULT 'pending',
    `payment_method` VARCHAR(50) DEFAULT 'mock_card',
    `transaction_ref` VARCHAR(100) UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
