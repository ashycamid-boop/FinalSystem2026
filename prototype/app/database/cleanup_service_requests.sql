-- Cleanup and migration script for `service_requests`
-- IMPORTANT: Run only after taking a full backup of your database.
-- Example backup via shell (on the host where MySQL runs):
-- mysqldump -u root -p your_database > backup_before_cleanup.sql
-- Or to backup only this table:
-- mysqldump -u root -p your_database service_requests > service_requests_backup.sql

START TRANSACTION;

-- 1) Backup current table inside the DB (quick snapshot)
DROP TABLE IF EXISTS service_requests_backup;
CREATE TABLE service_requests_backup AS SELECT * FROM service_requests;

-- 2) Find rows with NULL or empty ticket_no
SELECT id, ticket_no, created_at FROM service_requests WHERE ticket_no IS NULL OR TRIM(ticket_no) = '';

-- 3) Assign deterministic unique ticket_no to any rows missing one (uses id to guarantee uniqueness)
UPDATE service_requests
SET ticket_no = CONCAT('TCK-', LPAD(id, 8, '0'))
WHERE ticket_no IS NULL OR TRIM(ticket_no) = '';

-- 4) Identify duplicate ticket_no values and show affected rows
SELECT ticket_no, COUNT(*) AS cnt
FROM service_requests
GROUP BY ticket_no
HAVING COUNT(*) > 1;

-- 5) If duplicates exist, we will keep the earliest created_at (or the smallest id)
-- The following marks rows to keep and then deletes duplicates, preserving other data from the earliest row.
DROP TABLE IF EXISTS tmp_keep_service_requests;
CREATE TABLE tmp_keep_service_requests AS
SELECT MIN(id) AS keep_id, ticket_no
FROM service_requests
GROUP BY ticket_no
HAVING COUNT(*) > 1;

-- Delete duplicate rows (rows with same ticket_no but id <> keep_id)
DELETE sr
FROM service_requests sr
JOIN tmp_keep_service_requests k ON sr.ticket_no = k.ticket_no
WHERE sr.id <> k.keep_id;

-- 6) Optional: normalize NULLs for important text columns (set a readable default)
UPDATE service_requests
SET requester_name = COALESCE(NULLIF(TRIM(requester_name), ''), 'Unknown')
WHERE requester_name IS NULL OR TRIM(requester_name) = '';

UPDATE service_requests
SET requester_email = COALESCE(NULLIF(TRIM(requester_email), ''), 'no-reply@example.local')
WHERE requester_email IS NULL OR TRIM(requester_email) = '';

-- 7) Ensure ticket_no is not null and add a unique index (only run after duplicates removed)
ALTER TABLE service_requests
MODIFY ticket_no VARCHAR(64) NOT NULL;

-- If a unique key already exists this will error; attempt to drop and recreate safely
ALTER TABLE service_requests
DROP INDEX IF EXISTS uq_ticket_no;

ALTER TABLE service_requests
ADD UNIQUE INDEX uq_ticket_no (ticket_no);

COMMIT;

-- 8) Cleanup temporary helper table
DROP TABLE IF EXISTS tmp_keep_service_requests;

-- End of script. After running, verify results:
-- SELECT COUNT(*) FROM service_requests WHERE ticket_no IS NULL OR TRIM(ticket_no) = ''; -- should be 0
-- SELECT ticket_no, COUNT(*) FROM service_requests GROUP BY ticket_no HAVING COUNT(*) > 1; -- should be none
-- Review service_requests_backup before dropping it.
