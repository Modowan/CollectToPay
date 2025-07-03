-- =====================================================
-- BASE TENANT HILTON PARIS - EMAILS RÉALISTES
-- Compatible avec LoginController et middlewares
-- Base : collect_hotel_hilton_paris
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. TABLE CUSTOMERS (emails réalistes)
-- =====================================================

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `first_name` varchar(100) DEFAULT '',
  `last_name` varchar(100) DEFAULT '',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `postal_code` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relation` varchar(100) DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `special_requests` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `branch_id` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending_password','active','inactive') DEFAULT 'active',
  `is_activated` tinyint(1) NOT NULL DEFAULT 1,
  `activated_at` timestamp NULL DEFAULT NULL,
  `profile_completed` tinyint(1) NOT NULL DEFAULT 0,
  `last_profile_update` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `imported_from` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_email_index` (`email`),
  KEY `customers_branch_id_index` (`branch_id`),
  KEY `customers_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABLE BRANCHES (vraies branches Hilton)
-- =====================================================

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `manager_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branches_manager_id_index` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABLE TENANT_USERS
-- =====================================================

CREATE TABLE `tenant_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'manager',
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_users_email_index` (`email`),
  KEY `tenant_users_branch_id_index` (`branch_id`),
  KEY `tenant_users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLE BOOKINGS
-- =====================================================

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABLE BOOKING_SERVICES
-- =====================================================

CREATE TABLE `booking_services` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABLE BRANCH_EVENT_LOGS
-- =====================================================

CREATE TABLE `branch_event_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABLE BRANCH_SETTINGS
-- =====================================================

CREATE TABLE `branch_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABLE CUSTOMER_ACTIVATIONS
-- =====================================================

CREATE TABLE `customer_activations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABLE DATA_IMPORTS (tenant)
-- =====================================================

CREATE TABLE `data_imports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `import_type` enum('customers','bookings','payments') NOT NULL DEFAULT 'customers',
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `total_records` int(11) NOT NULL DEFAULT 0,
  `successful_records` int(11) NOT NULL DEFAULT 0,
  `failed_records` int(11) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. TABLE IMPORT_ERRORS (tenant)
-- =====================================================

CREATE TABLE `import_errors` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `field_name` varchar(255) DEFAULT NULL,
  `error_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. TABLE MIGRATIONS (tenant)
-- =====================================================

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. TABLE PASSWORD_RESET_TOKENS (tenant)
-- =====================================================

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_type` enum('customer','tenant_user') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. TABLE PAYMENT_CARDS
-- =====================================================

CREATE TABLE `payment_cards` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL UNIQUE,
  `last_four` varchar(4) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `exp_month` varchar(2) NOT NULL,
  `exp_year` varchar(4) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. TABLE PAYMENT_REFUNDS
-- =====================================================

CREATE TABLE `payment_refunds` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. TABLE PAYMENT_TRANSACTIONS
-- =====================================================

CREATE TABLE `payment_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. TABLE ROOMS
-- =====================================================

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `price_per_night` decimal(8,2) NOT NULL,
  `status` enum('available','occupied','maintenance') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. TABLE SERVICES
-- =====================================================

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. TABLE TENANT_NOTIFICATIONS
-- =====================================================

CREATE TABLE `tenant_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTION DES DONNÉES
-- =====================================================

-- Insertion des vraies branches Hilton
INSERT INTO `branches` (`id`, `name`, `address`, `city`, `country`, `phone`, `email`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'Hilton Paris Opéra', '108 Rue Saint-Lazare', 'Paris', 'France', '+33 1 40 08 44 44', 'paris.opera@hilton.com', 0, NOW(), NOW()),
(2, 'Hilton Paris Charles de Gaulle', 'Rue de Rome, Terminal 2', 'Roissy-en-France', 'France', '+33 1 49 19 77 77', 'paris.cdg@hilton.com', 0, NOW(), NOW()),
(3, 'Hilton Paris La Défense', '2 Place de la Défense', 'Paris La Défense', 'France', '+33 1 46 92 10 10', 'paris.defense@hilton.com', 0, NOW(), NOW()),
(4, 'Hilton Cannes', '50 Boulevard de la Croisette', 'Cannes', 'France', '+33 4 92 99 70 00', 'cannes@hilton.com', 0, NOW(), NOW()),
(5, 'Hilton Nice Palais de la Méditerranée', '15 Promenade des Anglais', 'Nice', 'France', '+33 4 92 14 77 00', 'nice@hilton.com', 0, NOW(), NOW());

-- Insertion des branch managers (mot de passe: "password")
INSERT INTO `tenant_users` (`id`, `name`, `email`, `password`, `role`, `branch_id`, `is_active`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Sophie Martin', 'sophie.martin@hilton.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'manager', 1, 1, NOW(), NOW(), NOW()),
(2, 'Jean-Luc Dubois', 'jeanluc.dubois@hilton.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'manager', 2, 1, NOW(), NOW(), NOW()),
(3, 'Amélie Rousseau', 'amelie.rousseau@hilton.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'manager', 3, 1, NOW(), NOW(), NOW()),
(4, 'Marc Leroy', 'marc.leroy@hilton.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'manager', 4, 1, NOW(), NOW(), NOW()),
(5, 'Isabelle Moreau', 'isabelle.moreau@hilton.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'manager', 5, 1, NOW(), NOW(), NOW());

-- Insertion des customers avec emails réalistes (mot de passe: "password")
INSERT INTO `customers` (`id`, `name`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `city`, `country`, `branch_id`, `status`, `is_activated`, `profile_completed`, `created_at`, `updated_at`) VALUES
(1, 'Ahmed Hassan', 'Ahmed', 'Hassan', 'ahmed.hassan@gmail.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 12 34 56 78', '15 Rue de la Paix', 'Paris', 'France', 1, 'active', 1, 0, NOW(), NOW()),
(2, 'Marie Dubois', 'Marie', 'Dubois', 'marie.dubois@gmail.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 23 45 67 89', '25 Avenue des Champs', 'Paris', 'France', 1, 'active', 1, 0, NOW(), NOW()),
(3, 'Pierre Martin', 'Pierre', 'Martin', 'pierre.martin@yahoo.fr', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 34 56 78 90', '30 Boulevard Haussmann', 'Paris', 'France', 2, 'active', 1, 0, NOW(), NOW()),
(4, 'Sophie Leroy', 'Sophie', 'Leroy', 'sophie.leroy@yahoo.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 45 67 89 01', '40 Rue de Rivoli', 'Paris', 'France', 2, 'active', 1, 0, NOW(), NOW()),
(5, 'Jean Moreau', 'Jean', 'Moreau', 'jean.moreau@hotmail.fr', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 56 78 90 12', '50 Place Vendôme', 'Paris', 'France', 3, 'active', 1, 0, NOW(), NOW()),
(6, 'Nadia Benali', 'Nadia', 'Benali', 'nadia.benali@outlook.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 67 89 01 23', '60 Avenue Montaigne', 'Paris', 'France', 3, 'active', 1, 0, NOW(), NOW()),
(7, 'Thomas Rousseau', 'Thomas', 'Rousseau', 'thomas.rousseau@orange.fr', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 78 90 12 34', '70 Rue Saint-Honoré', 'Paris', 'France', 4, 'active', 1, 0, NOW(), NOW()),
(8, 'Fatima Ali', 'Fatima', 'Ali', 'fatima.ali@orange.fr', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 89 01 23 45', '80 Boulevard Saint-Germain', 'Paris', 'France', 4, 'active', 1, 0, NOW(), NOW()),
(9, 'Michel Blanc', 'Michel', 'Blanc', 'michel.blanc@free.fr', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 90 12 34 56', '90 Rue de la République', 'Lyon', 'France', 5, 'active', 1, 0, NOW(), NOW()),
(10, 'Layla Mahmoud', 'Layla', 'Mahmoud', 'layla.mahmoud@free.fr', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', '+33 6 01 23 45 67', '100 Avenue Jean Médecin', 'Nice', 'France', 5, 'active', 1, 0, NOW(), NOW());

-- Mise à jour des manager_id dans les branches
UPDATE `branches` SET `manager_id` = 1 WHERE `id` = 1;
UPDATE `branches` SET `manager_id` = 2 WHERE `id` = 2;
UPDATE `branches` SET `manager_id` = 3 WHERE `id` = 3;
UPDATE `branches` SET `manager_id` = 4 WHERE `id` = 4;
UPDATE `branches` SET `manager_id` = 5 WHERE `id` = 5;

-- Mise à jour des clés auto-increment
ALTER TABLE `branches` AUTO_INCREMENT = 6;
ALTER TABLE `tenant_users` AUTO_INCREMENT = 6;
ALTER TABLE `customers` AUTO_INCREMENT = 11;

COMMIT;

