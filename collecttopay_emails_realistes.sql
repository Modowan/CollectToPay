-- =====================================================
-- BASE CENTRALE COLLECTTOPAY - EMAILS RÉALISTES
-- Compatible avec LoginController et middlewares
-- Base : collecttopay
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. TABLE USERS (admins et hotel managers)
-- =====================================================

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','hotel_manager','branch_manager') NOT NULL DEFAULT 'hotel_manager',
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_email_index` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABLE TENANTS (hôtels avec vraies données)
-- =====================================================

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL UNIQUE,
  `database_name` varchar(255) NOT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `max_users` int(11) NOT NULL DEFAULT 50,
  `max_branches` int(11) NOT NULL DEFAULT 10,
  `subscription_plan` varchar(100) DEFAULT 'standard',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenants_domain_index` (`domain`),
  KEY `tenants_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABLE API_TOKENS
-- =====================================================

CREATE TABLE `api_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hotel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `abilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abilities`)),
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `revoked_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_tokens_user_id_index` (`user_id`),
  KEY `api_tokens_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLE BACKUP_LOGS
-- =====================================================

CREATE TABLE `backup_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hotel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `backup_type` enum('system','tenant','hotel','all') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','failed') NOT NULL,
  `message` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABLE DATA_IMPORTS
-- =====================================================

CREATE TABLE `data_imports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `total_records` int(11) NOT NULL DEFAULT 0,
  `successful_records` int(11) NOT NULL DEFAULT 0,
  `failed_records` int(11) NOT NULL DEFAULT 0,
  `preview_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preview_data`)),
  `mapping_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mapping_config`)),
  `error_message` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `processing_time` int(11) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABLE EVENT_LOGS
-- =====================================================

CREATE TABLE `event_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hotel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_type` varchar(255) DEFAULT NULL,
  `event_type` varchar(255) NOT NULL,
  `entity_type` varchar(255) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABLE FAILED_JOBS
-- =====================================================

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL UNIQUE,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABLE HOTELS
-- =====================================================

CREATE TABLE `hotels` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `database_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABLE HOTEL_SETTINGS
-- =====================================================

CREATE TABLE `hotel_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `value_type` enum('string','integer','float','boolean','json','array') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. TABLE IMPORT_ERRORS
-- =====================================================

CREATE TABLE `import_errors` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `field_name` varchar(255) DEFAULT NULL,
  `field_value` text DEFAULT NULL,
  `error_type` varchar(100) NOT NULL,
  `error_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. TABLE MIGRATIONS
-- =====================================================

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. TABLE NOTIFICATIONS
-- =====================================================

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. TABLE PASSWORD_RESET_TOKENS
-- =====================================================

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. TABLE PERSONAL_ACCESS_TOKENS
-- =====================================================

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL UNIQUE,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. TABLE SYSTEM_SETTINGS
-- =====================================================

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL UNIQUE,
  `value` text DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'string',
  `group` varchar(100) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. TABLE TENANT_SETTINGS
-- =====================================================

CREATE TABLE `tenant_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `value_type` enum('string','integer','float','boolean','json','array') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTION DES DONNÉES
-- =====================================================

-- Insertion des tenants
INSERT INTO `tenants` (`id`, `name`, `domain`, `database_name`, `status`, `max_users`, `max_branches`, `subscription_plan`, `subscription_expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Hilton Hotels France', 'hilton-france.collecttopay.com', 'Collect_hotel_hilton_paris', 'active', 100, 20, 'premium', '2025-12-31 23:59:59', NOW(), NOW()),
(2, 'Marriott Hotels France', 'marriott-france.collecttopay.com', 'Collect_hotel_marriott_lyon', 'active', 80, 15, 'premium', '2025-12-31 23:59:59', NOW(), NOW()),
(3, 'Accor Hotels Nice', 'accor-nice.collecttopay.com', 'Collect_hotel_accor_nice', 'active', 60, 12, 'standard', '2025-12-31 23:59:59', NOW(), NOW()),
(4, 'Sofitel Hotels Marseille', 'sofitel-marseille.collecttopay.com', 'Collect_hotel_sofitel_marseille', 'active', 70, 10, 'premium', '2025-12-31 23:59:59', NOW(), NOW()),
(5, 'Ibis Hotels France', 'ibis-france.collecttopay.com', 'Collect_hotel_ibis_toulouse', 'active', 120, 25, 'standard', '2025-12-31 23:59:59', NOW(), NOW());

-- Insertion des users (mot de passe: "password")
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `tenant_id`, `is_active`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Administrateur Principal', 'admin@collecttopay.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'admin', NULL, 1, NOW(), NOW(), NOW()),
(2, 'Admin Technique', 'tech@collecttopay.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'admin', NULL, 1, NOW(), NOW(), NOW()),
(3, 'Directeur Hilton France', 'directeur@hilton-france.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'hotel_manager', 1, 1, NOW(), NOW(), NOW()),
(4, 'Manager Marriott France', 'manager@marriott-france.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'hotel_manager', 2, 1, NOW(), NOW(), NOW()),
(5, 'Responsable Accor Nice', 'responsable@accor-nice.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'hotel_manager', 3, 1, NOW(), NOW(), NOW()),
(6, 'Directeur Sofitel Marseille', 'directeur@sofitel-marseille.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'hotel_manager', 4, 1, NOW(), NOW(), NOW()),
(7, 'Manager Ibis France', 'manager@ibis-france.com', '$2y$12$JZCDDg79FNShoM5zE5n05OZkrcQxjLMUwrpbkutBP/XIxUFDi0LL2', 'hotel_manager', 5, 1, NOW(), NOW(), NOW());

-- Insertion des API tokens
INSERT INTO `api_tokens` (`id`, `user_id`, `tenant_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Admin API Token', 'abcd1234567890efghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqr', '["admin:read", "admin:write", "imports:manage"]', '2025-06-06 10:30:00', '2026-06-06 10:30:00', '2025-06-06 09:00:00', '2025-06-06 10:30:00'),
(2, 3, 1, 'Hilton Manager API Token', 'xyz9876543210fedcba0987654321abcdefghijklmnopqrstuvwxyz0987654321', '["hotel:read", "hotel:write", "imports:read"]', '2025-06-06 14:15:00', '2025-12-06 14:15:00', '2025-06-06 14:00:00', '2025-06-06 14:15:00');

-- Mise à jour des clés auto-increment
ALTER TABLE `users` AUTO_INCREMENT = 8;
ALTER TABLE `tenants` AUTO_INCREMENT = 6;
ALTER TABLE `api_tokens` AUTO_INCREMENT = 3;

COMMIT;

