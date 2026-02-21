-- Migration: add vehicle status and remarks to spot_report_vehicles
-- Run this SQL on your MySQL/MariaDB database.

ALTER TABLE spot_report_vehicles
  ADD COLUMN `status` VARCHAR(64) NULL AFTER `engine`;

ALTER TABLE spot_report_vehicles
  ADD COLUMN `remarks` TEXT NULL AFTER `status`;
