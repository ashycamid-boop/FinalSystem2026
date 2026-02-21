-- Add updated_at column to spot_reports to track last updates
ALTER TABLE `spot_reports`
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;
