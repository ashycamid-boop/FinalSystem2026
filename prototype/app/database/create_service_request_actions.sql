-- Migration: create service_request_actions table
-- Run after backing up your DB

CREATE TABLE IF NOT EXISTS `service_request_actions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_request_id` INT UNSIGNED NOT NULL,
  `action_date` DATE NULL,
  `action_time` TIME NULL,
  `action_details` TEXT NULL,
  `action_staff_id` INT NULL,
  `action_signature_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_request_id` (`service_request_id`),
  CONSTRAINT `fk_sra_request` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
