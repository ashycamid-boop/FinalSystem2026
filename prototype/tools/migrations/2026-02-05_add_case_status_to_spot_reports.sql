-- Migration: add case_status column to spot_reports
-- Run this against your database (MySQL)
ALTER TABLE `spot_reports`
  ADD COLUMN `case_status` VARCHAR(64) DEFAULT NULL AFTER `status`;

-- Optionally backfill for already approved reports (set default case status to 'under-investigation')
UPDATE `spot_reports` SET `case_status` = 'under-investigation' WHERE LOWER(TRIM(`status`)) = 'approved' AND (`case_status` IS NULL OR `case_status` = '');
