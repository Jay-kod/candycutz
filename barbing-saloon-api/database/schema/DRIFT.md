diff --git "a/barbing-saloon-api\\database\\schema\\production-schema.sql" "b/barbing-saloon-api\\database\\schema\\fresh-schema.sql"
index b1b7d82..f554322 100644
--- "a/barbing-saloon-api\\database\\schema\\production-schema.sql"
+++ "b/barbing-saloon-api\\database\\schema\\fresh-schema.sql"
@@ -1,6 +1,6 @@
 -- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
 --
--- Host: localhost    Database: candycutz_db
+-- Host: localhost    Database: candycutz_test
 -- ------------------------------------------------------
 -- Server version	10.4.32-MariaDB
 
@@ -53,7 +53,7 @@ CREATE TABLE `appointment_items` (
   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   `appointment_id` bigint(20) unsigned NOT NULL,
   `service_id` bigint(20) unsigned NOT NULL,
-  `price` decimal(10,2) NOT NULL,
+  `price` bigint(20) NOT NULL,
   `duration_minutes` int(11) NOT NULL DEFAULT 30,
   `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
@@ -111,37 +111,37 @@ CREATE TABLE `appointments` (
   `appointment_time` time NOT NULL,
   `end_time` time DEFAULT NULL,
   `total_duration_minutes` int(11) NOT NULL DEFAULT 30,
-  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
-  `travel_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
-  `tip_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
-  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
-  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
+  `total_amount` bigint(20) NOT NULL DEFAULT 0,
+  `travel_fee` bigint(20) NOT NULL DEFAULT 0,
+  `tip_amount` bigint(20) NOT NULL DEFAULT 0,
+  `discount_amount` bigint(20) NOT NULL DEFAULT 0,
+  `grand_total` bigint(20) NOT NULL DEFAULT 0,
   `status` enum('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
   `notes` text DEFAULT NULL,
   `cancellation_reason` text DEFAULT NULL,
-  `total_price` decimal(8,2) NOT NULL,
+  `total_price` bigint(20) NOT NULL,
   `deposit_paid` tinyint(1) NOT NULL DEFAULT 0,
-  `deposit_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
+  `deposit_amount` bigint(20) NOT NULL DEFAULT 0,
   `deleted_at` timestamp NULL DEFAULT NULL,
   `created_at` timestamp NULL DEFAULT NULL,
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `appointments_booking_reference_unique` (`booking_reference`),
-  KEY `appointments_customer_id_foreign` (`customer_id`),
-  KEY `appointments_barber_id_foreign` (`barber_id`),
-  KEY `appointments_service_id_foreign` (`service_id`),
-  KEY `appointments_appointment_date_barber_id_index` (`appointment_date`,`barber_id`),
-  KEY `appointments_status_index` (`status`),
   KEY `appointments_branch_id_foreign` (`branch_id`),
+  KEY `appointments_service_id_foreign` (`service_id`),
   KEY `appointments_service_zone_id_foreign` (`service_zone_id`),
   KEY `appointments_customer_address_id_foreign` (`customer_address_id`),
+  KEY `appointments_appointment_date_barber_id_index` (`appointment_date`,`barber_id`),
+  KEY `appointments_status_index` (`status`),
+  KEY `appointments_barber_date_status_idx` (`barber_id`,`appointment_date`,`status`),
+  KEY `appointments_customer_status_idx` (`customer_id`,`status`),
   CONSTRAINT `appointments_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`),
   CONSTRAINT `appointments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
   CONSTRAINT `appointments_customer_address_id_foreign` FOREIGN KEY (`customer_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
   CONSTRAINT `appointments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
   CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
   CONSTRAINT `appointments_service_zone_id_foreign` FOREIGN KEY (`service_zone_id`) REFERENCES `service_zones` (`id`) ON DELETE SET NULL
-) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -168,7 +168,7 @@ CREATE TABLE `audit_logs` (
   PRIMARY KEY (`id`),
   KEY `audit_logs_user_id_foreign` (`user_id`),
   CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -182,7 +182,7 @@ CREATE TABLE `barber_services` (
   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   `barber_id` bigint(20) unsigned NOT NULL,
   `service_id` bigint(20) unsigned NOT NULL,
-  `custom_price` decimal(10,2) DEFAULT NULL,
+  `custom_price` bigint(20) DEFAULT NULL,
   `custom_duration` int(11) DEFAULT NULL,
   `is_offered` tinyint(1) NOT NULL DEFAULT 1,
   `created_at` timestamp NULL DEFAULT NULL,
@@ -192,7 +192,7 @@ CREATE TABLE `barber_services` (
   KEY `barber_services_service_id_foreign` (`service_id`),
   CONSTRAINT `barber_services_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`) ON DELETE CASCADE,
   CONSTRAINT `barber_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
-) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -213,19 +213,19 @@ CREATE TABLE `barbers` (
   `display_order` int(10) unsigned NOT NULL DEFAULT 0,
   `is_featured` tinyint(1) NOT NULL DEFAULT 0,
   `is_available` tinyint(1) NOT NULL DEFAULT 1,
-  `created_at` timestamp NULL DEFAULT NULL,
-  `updated_at` timestamp NULL DEFAULT NULL,
   `is_home_service_ready` tinyint(1) NOT NULL DEFAULT 1,
   `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
   `total_reviews` int(11) NOT NULL DEFAULT 0,
   `chair_status` varchar(20) NOT NULL DEFAULT 'free',
   `experience_years` int(10) unsigned DEFAULT NULL,
+  `created_at` timestamp NULL DEFAULT NULL,
+  `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `barbers_user_id_unique` (`user_id`),
   KEY `barbers_branch_id_foreign` (`branch_id`),
   CONSTRAINT `barbers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
   CONSTRAINT `barbers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -247,7 +247,7 @@ CREATE TABLE `blocked_periods` (
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `blocked_periods_created_by_foreign` (`created_by`),
-  KEY `blocked_periods_barber_id_start_datetime_end_datetime_index` (`barber_id`,`start_datetime`,`end_datetime`),
+  KEY `blocked_periods_b_id_start_end_idx` (`barber_id`,`start_datetime`,`end_datetime`),
   CONSTRAINT `blocked_periods_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`) ON DELETE CASCADE,
   CONSTRAINT `blocked_periods_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -277,7 +277,7 @@ CREATE TABLE `blog_posts` (
   UNIQUE KEY `blog_posts_slug_unique` (`slug`),
   KEY `blog_posts_author_id_foreign` (`author_id`),
   CONSTRAINT `blog_posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -293,7 +293,7 @@ CREATE TABLE `branches` (
   `name` varchar(255) NOT NULL,
   `slug` varchar(100) NOT NULL,
   `address` text NOT NULL,
-  `latitude` decimal(10,8) NOT NULL DEFAULT 8.84710000,
+  `latitude` decimal(10,8) NOT NULL DEFAULT 8.84860000,
   `longitude` decimal(11,8) NOT NULL DEFAULT 7.87360000,
   `phone` varchar(30) NOT NULL,
   `email` varchar(255) NOT NULL,
@@ -304,7 +304,7 @@ CREATE TABLE `branches` (
   UNIQUE KEY `branches_slug_unique` (`slug`),
   KEY `branches_business_id_foreign` (`business_id`),
   CONSTRAINT `branches_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -324,7 +324,7 @@ CREATE TABLE `businesses` (
   `created_at` timestamp NULL DEFAULT NULL,
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -401,7 +401,7 @@ CREATE TABLE `gallery` (
   PRIMARY KEY (`id`),
   KEY `gallery_barber_id_foreign` (`barber_id`),
   CONSTRAINT `gallery_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -434,7 +434,7 @@ CREATE TABLE `migrations` (
   `migration` varchar(255) NOT NULL,
   `batch` int(11) NOT NULL,
   PRIMARY KEY (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -494,7 +494,7 @@ CREATE TABLE `payment_transactions` (
   `transaction_type` enum('authorization','capture','refund','void') NOT NULL,
   `gateway` varchar(50) NOT NULL DEFAULT 'stripe',
   `gateway_event_id` varchar(150) DEFAULT NULL,
-  `amount` decimal(10,2) NOT NULL,
+  `amount` bigint(20) NOT NULL,
   `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_payload`)),
   `status` varchar(50) NOT NULL,
   `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
@@ -516,12 +516,12 @@ CREATE TABLE `payments` (
   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   `appointment_id` bigint(20) unsigned NOT NULL,
   `customer_id` bigint(20) unsigned NOT NULL,
-  `amount` decimal(10,2) NOT NULL,
+  `amount` bigint(20) NOT NULL,
   `currency` varchar(10) NOT NULL DEFAULT 'NGN',
   `status` enum('pending','successful','failed','refunded') NOT NULL DEFAULT 'pending',
   `payment_method` varchar(50) NOT NULL DEFAULT 'stripe',
-  `stripe_payment_intent_id` varchar(150) DEFAULT NULL,
-  `stripe_charge_id` varchar(150) DEFAULT NULL,
+  `gateway_reference` varchar(150) DEFAULT NULL,
+  `gateway_charge_id` varchar(150) DEFAULT NULL,
   `transaction_ref` varchar(100) NOT NULL,
   `receipt_url` varchar(255) DEFAULT NULL,
   `error_message` text DEFAULT NULL,
@@ -531,7 +531,7 @@ CREATE TABLE `payments` (
   UNIQUE KEY `payments_transaction_ref_unique` (`transaction_ref`),
   KEY `payments_customer_id_foreign` (`customer_id`),
   KEY `payments_appointment_id_status_index` (`appointment_id`,`status`),
-  KEY `payments_stripe_payment_intent_id_index` (`stripe_payment_intent_id`),
+  KEY `payments_stripe_payment_intent_id_index` (`gateway_reference`),
   CONSTRAINT `payments_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
   CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -548,7 +548,7 @@ CREATE TABLE `personal_access_tokens` (
   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   `tokenable_type` varchar(255) NOT NULL,
   `tokenable_id` bigint(20) unsigned NOT NULL,
-  `name` text NOT NULL,
+  `name` varchar(255) NOT NULL,
   `token` varchar(64) NOT NULL,
   `abilities` text DEFAULT NULL,
   `last_used_at` timestamp NULL DEFAULT NULL,
@@ -557,9 +557,8 @@ CREATE TABLE `personal_access_tokens` (
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
-  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
-  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
-) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -583,7 +582,7 @@ CREATE TABLE `service_categories` (
   UNIQUE KEY `service_categories_slug_unique` (`slug`),
   KEY `service_categories_branch_id_foreign` (`branch_id`),
   CONSTRAINT `service_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
-) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -600,15 +599,15 @@ CREATE TABLE `service_zones` (
   `description` text DEFAULT NULL,
   `boundary_polygon` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`boundary_polygon`)),
   `radius_km` decimal(6,2) NOT NULL DEFAULT 15.00,
-  `base_travel_fee` decimal(10,2) NOT NULL DEFAULT 2000.00,
-  `per_km_fee` decimal(10,2) NOT NULL DEFAULT 150.00,
+  `base_travel_fee` bigint(20) NOT NULL DEFAULT 200000,
+  `per_km_fee` bigint(20) NOT NULL DEFAULT 15000,
   `is_active` tinyint(1) NOT NULL DEFAULT 1,
   `created_at` timestamp NULL DEFAULT NULL,
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `service_zones_branch_id_foreign` (`branch_id`),
   CONSTRAINT `service_zones_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
-) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -624,7 +623,7 @@ CREATE TABLE `services` (
   `name` varchar(255) NOT NULL,
   `slug` varchar(255) NOT NULL,
   `description` text NOT NULL,
-  `price` decimal(8,2) NOT NULL,
+  `price` bigint(20) NOT NULL,
   `duration_minutes` int(10) unsigned NOT NULL,
   `home_service_allowed` tinyint(1) NOT NULL DEFAULT 1,
   `category_id` bigint(20) unsigned NOT NULL,
@@ -638,11 +637,11 @@ CREATE TABLE `services` (
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `services_slug_unique` (`slug`),
-  KEY `services_category_id_foreign` (`category_id`),
   KEY `services_branch_id_foreign` (`branch_id`),
+  KEY `services_category_id_foreign` (`category_id`),
   CONSTRAINT `services_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
   CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -661,7 +660,7 @@ CREATE TABLE `settings` (
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `settings_key_unique` (`key`)
-) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -712,7 +711,7 @@ CREATE TABLE `theme_settings` (
   PRIMARY KEY (`id`),
   KEY `theme_settings_branch_id_foreign` (`branch_id`),
   CONSTRAINT `theme_settings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -735,7 +734,7 @@ CREATE TABLE `theme_versions` (
   KEY `theme_versions_published_by_user_id_foreign` (`published_by_user_id`),
   CONSTRAINT `theme_versions_published_by_user_id_foreign` FOREIGN KEY (`published_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
   CONSTRAINT `theme_versions_theme_setting_id_foreign` FOREIGN KEY (`theme_setting_id`) REFERENCES `theme_settings` (`id`) ON DELETE CASCADE
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -753,7 +752,7 @@ CREATE TABLE `users` (
   `email` varchar(255) NOT NULL,
   `password` varchar(255) NOT NULL,
   `role` enum('super_admin','admin','barber','customer') NOT NULL,
-  `auth_provider` varchar(50) DEFAULT 'local',
+  `auth_provider` varchar(50) NOT NULL DEFAULT 'local',
   `provider_id` varchar(150) DEFAULT NULL,
   `status` enum('active','username_pending','deactivated','suspended') NOT NULL DEFAULT 'active',
   `last_username_change_at` timestamp NULL DEFAULT NULL,
@@ -769,7 +768,7 @@ CREATE TABLE `users` (
   PRIMARY KEY (`id`),
   UNIQUE KEY `users_email_unique` (`email`),
   UNIQUE KEY `users_username_unique` (`username`)
-) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 
 --
@@ -791,7 +790,7 @@ CREATE TABLE `working_hours` (
   PRIMARY KEY (`id`),
   KEY `working_hours_barber_id_foreign` (`barber_id`),
   CONSTRAINT `working_hours_barber_id_foreign` FOREIGN KEY (`barber_id`) REFERENCES `barbers` (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
 /*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
 
