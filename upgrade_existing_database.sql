USE library_management;

-- Upgrade an EXISTING database from the original Library Management System.
-- This script is intended for XAMPP / MySQL / MariaDB.
-- It is safe to run after the earlier upgrade script as well.

-- Pending requests do not have a borrowed/due date until an admin approves them.
ALTER TABLE borrowings
    MODIFY borrowed_at DATETIME NULL,
    MODIFY due_date DATETIME NULL,
    MODIFY status ENUM('pending','borrowed','returned') NOT NULL DEFAULT 'pending';

-- New borrowing-workflow fields. IF NOT EXISTS prevents duplicate-column errors.
ALTER TABLE borrowings
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER book_id,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER created_at,
    ADD COLUMN IF NOT EXISTS fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER returned_at,
    ADD COLUMN IF NOT EXISTS fine_paid TINYINT(1) NOT NULL DEFAULT 0 AFTER fine_amount;

-- Existing old borrowing rows were already approved/borrowed, so give them an
-- approval timestamp where possible. Do not change their current status.
UPDATE borrowings
SET approved_at = borrowed_at
WHERE status = 'borrowed' AND approved_at IS NULL AND borrowed_at IS NOT NULL;

-- Keep created_at meaningful for old records when it was newly added.
UPDATE borrowings
SET created_at = COALESCE(borrowed_at, CURRENT_TIMESTAMP)
WHERE created_at IS NULL;
