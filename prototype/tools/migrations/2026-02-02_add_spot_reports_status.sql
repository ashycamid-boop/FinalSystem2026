-- Migration: add status column to spot_reports
-- Adds a `status` field with sensible defaults and allowed values.

ALTER TABLE spot_reports
  ADD COLUMN `status` ENUM('Draft','Pending','Approved','Rejected','Under Review') NOT NULL DEFAULT 'Pending';

-- Set existing NULL/empty values to 'Pending' (defensive):
UPDATE spot_reports SET `status` = 'Pending' WHERE `status` IS NULL OR `status` = '';

-- Optional: add an index if you'll query by status often
ALTER TABLE spot_reports ADD INDEX idx_spot_reports_status (`status`);
