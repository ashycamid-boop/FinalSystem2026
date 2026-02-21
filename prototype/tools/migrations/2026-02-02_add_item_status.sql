-- Migration: add item status to spot_report_items
-- Run this SQL on your MySQL/MariaDB database.

ALTER TABLE spot_report_items
  ADD COLUMN `status` VARCHAR(64) NULL AFTER `remarks`;
