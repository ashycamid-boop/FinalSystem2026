-- Migration: add submitted_by column to spot_reports
-- Run this SQL on your MySQL/MariaDB database.

ALTER TABLE spot_reports
  ADD COLUMN `submitted_by` INT NULL AFTER `status`;

ALTER TABLE spot_reports
  ADD CONSTRAINT `fk_spot_reports_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES users(`id`) ON DELETE SET NULL;
