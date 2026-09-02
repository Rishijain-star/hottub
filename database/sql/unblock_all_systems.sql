-- ============================================================
-- UNBLOCK ALL DEVICES (security rules stay active after this)
-- Run in phpMyAdmin → SQL tab → Go
-- ============================================================

-- 1) Remove all abuse / captcha blocks from database
DELETE FROM geo_blocked_identifiers;

-- 2) Unblock registration attempts marked blocked in registered_users
UPDATE registered_users
SET status = 'started', block_reason = NULL
WHERE status = 'blocked';

-- 3) Verify — should return 0 rows (or only non-blocked users)
SELECT COUNT(*) AS remaining_geo_blocks FROM geo_blocked_identifiers;

SELECT id, status, block_reason, hardware_profile_hash, created_at
FROM registered_users
WHERE status = 'blocked' OR block_reason IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;

-- ============================================================
-- ALSO REQUIRED (File Manager — not SQL):
-- Delete folder contents:
--   /public_html/site-app/storage/framework/cache/data/
--   /public_html/site-app/bootstrap/cache/config.php (if exists)
--
-- This clears 24-hour OTP hardware locks (otp_hw_lock:* in cache).
-- ============================================================
