-- Spot reports schema

CREATE TABLE `spot_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference_no` VARCHAR(100) NOT NULL UNIQUE,
  `incident_datetime` DATETIME NULL,
  `memo_date` DATETIME NULL,
  `location` TEXT NULL,
  `summary` TEXT NULL,
  `team_leader` VARCHAR(255) NULL,
  `custodian` VARCHAR(255) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Draft',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `spot_report_persons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  `age` VARCHAR(50) NULL,
  `gender` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `contact` VARCHAR(100) NULL,
  `role` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX (`report_id`),
  CONSTRAINT `fk_srp_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `spot_report_vehicles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` INT UNSIGNED NOT NULL,
  `plate` VARCHAR(100) NULL,
  `make` VARCHAR(255) NULL,
  `color` VARCHAR(100) NULL,
  `owner` VARCHAR(255) NULL,
  `contact` VARCHAR(100) NULL,
  `engine` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX (`report_id`),
  CONSTRAINT `fk_srv_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `spot_report_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` INT UNSIGNED NOT NULL,
  `item_no` VARCHAR(50) NULL,
  `type` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `quantity` VARCHAR(100) NULL,
  `volume` VARCHAR(100) NULL,
  `value` DECIMAL(15,2) NULL,
  `remarks` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX (`report_id`),
  CONSTRAINT `fk_sri_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `spot_report_files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` INT UNSIGNED NOT NULL,
  `file_type` VARCHAR(50) NOT NULL, -- evidence|pdf
  `file_path` VARCHAR(1024) NOT NULL,
  `orig_name` VARCHAR(512) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`report_id`),
  CONSTRAINT `fk_srf_report` FOREIGN KEY (`report_id`) REFERENCES `spot_reports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
