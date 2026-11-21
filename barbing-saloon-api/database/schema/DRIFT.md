diff --git "a/database\\schema\\production-schema.sql" "b/database\\schema\\fresh-schema.sql"
index b1b7d82..c8ff3b3 100644
--- "a/database\\schema\\production-schema.sql"
+++ "b/database\\schema\\fresh-schema.sql"
@@ -3 +3 @@
--- Host: localhost    Database: candycutz_db
+-- Host: localhost    Database: candycutz_test
@@ -129,0 +130 @@ CREATE TABLE `appointments` (
+  KEY `appointments_branch_id_foreign` (`branch_id`),
@@ -133,3 +133,0 @@ CREATE TABLE `appointments` (
-  KEY `appointments_appointment_date_barber_id_index` (`appointment_date`,`barber_id`),
-  KEY `appointments_status_index` (`status`),
-  KEY `appointments_branch_id_foreign` (`branch_id`),
@@ -137,0 +136,2 @@ CREATE TABLE `appointments` (
+  KEY `appointments_appointment_date_barber_id_index` (`appointment_date`,`barber_id`),
+  KEY `appointments_status_index` (`status`),
@@ -144 +144 @@ CREATE TABLE `appointments` (
-) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -171 +171 @@ CREATE TABLE `audit_logs` (
-) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -195 +195 @@ CREATE TABLE `barber_services` (
-) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -216,2 +215,0 @@ CREATE TABLE `barbers` (
-  `created_at` timestamp NULL DEFAULT NULL,
-  `updated_at` timestamp NULL DEFAULT NULL,
@@ -222,0 +221,2 @@ CREATE TABLE `barbers` (
+  `created_at` timestamp NULL DEFAULT NULL,
+  `updated_at` timestamp NULL DEFAULT NULL,
@@ -228 +228 @@ CREATE TABLE `barbers` (
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -250 +250 @@ CREATE TABLE `blocked_periods` (
-  KEY `blocked_periods_barber_id_start_datetime_end_datetime_index` (`barber_id`,`start_datetime`,`end_datetime`),
+  KEY `blocked_periods_b_id_start_end_idx` (`barber_id`,`start_datetime`,`end_datetime`),
@@ -280 +280 @@ CREATE TABLE `blog_posts` (
-) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -296 +296 @@ CREATE TABLE `branches` (
-  `latitude` decimal(10,8) NOT NULL DEFAULT 8.84710000,
+  `latitude` decimal(10,8) NOT NULL DEFAULT 8.84860000,
@@ -307 +307 @@ CREATE TABLE `branches` (
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -327 +327 @@ CREATE TABLE `businesses` (
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -404 +404 @@ CREATE TABLE `gallery` (
-) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -437 +437 @@ CREATE TABLE `migrations` (
-) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -562 +562 @@ CREATE TABLE `personal_access_tokens` (
-) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -586 +586 @@ CREATE TABLE `service_categories` (
-) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -611 +611 @@ CREATE TABLE `service_zones` (
-) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -641 +640,0 @@ CREATE TABLE `services` (
-  KEY `services_category_id_foreign` (`category_id`),
@@ -642,0 +642 @@ CREATE TABLE `services` (
+  KEY `services_category_id_foreign` (`category_id`),
@@ -645 +645 @@ CREATE TABLE `services` (
-) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -664 +664 @@ CREATE TABLE `settings` (
-) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -715 +715 @@ CREATE TABLE `theme_settings` (
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -738 +738 @@ CREATE TABLE `theme_versions` (
-) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -772 +772 @@ CREATE TABLE `users` (
-) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -794 +794 @@ CREATE TABLE `working_hours` (
-) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
@@ -806 +806 @@ CREATE TABLE `working_hours` (
--- Dump completed on 2026-09-18  7:22:39
+-- Dump completed on 2026-09-18  7:47:57
