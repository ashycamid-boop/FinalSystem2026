-- Migration: add auth1/auth2 metadata columns to service_requests
ALTER TABLE `service_requests`
  ADD COLUMN `auth1_name` VARCHAR(255) NULL AFTER `requester_signature_path`,
  ADD COLUMN `auth1_position` VARCHAR(150) NULL AFTER `auth1_name`,
  ADD COLUMN `auth1_date` DATE NULL AFTER `auth1_position`,
  ADD COLUMN `auth2_name` VARCHAR(255) NULL AFTER `auth1_signature_path`,
  ADD COLUMN `auth2_position` VARCHAR(150) NULL AFTER `auth2_name`,
  ADD COLUMN `auth2_date` DATE NULL AFTER `auth2_position`;

-- To apply: from your project root run (adjust DB credentials as needed):
-- mysql -u root -p your_database_name < app/database/add_auth_fields_service_requests.sql
