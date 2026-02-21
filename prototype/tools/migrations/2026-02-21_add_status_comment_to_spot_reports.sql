-- Migration: add status_comment to spot_reports
ALTER TABLE `spot_reports`
  ADD COLUMN `status_comment` TEXT NULL AFTER `status`;
