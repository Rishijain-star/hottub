-- Admin 2FA SMS troubleshooting (phpMyAdmin)
-- Replace 7367524129 with the last digits of the admin mobile shown on /admin/two-factor

-- 1) Is this phone blocked?
SELECT * FROM geo_blocked_identifiers
WHERE type = 'phone' AND identifier LIKE '%7367524129%';

-- 2) OTP hardware / abuse locks (clear only if you intend to unblock)
SELECT * FROM geo_blocked_identifiers
WHERE type IN ('hw_profiles', 'devices', 'fingerprints', 'persistent_ids', 'geo_coords')
ORDER BY id DESC
LIMIT 20;

-- 3) Admin user phone on file (this row is NOT deleted when unblocking)
SELECT id, email, phone, role FROM users
WHERE role IN ('admin', 'sub_admin')
ORDER BY id;

-- ============================================================
-- UNBLOCK phone only (removes block list entry — does NOT delete
-- the mobile number from users table; admin phone stays the same)
-- ============================================================
DELETE FROM geo_blocked_identifiers
WHERE type = 'phone' AND identifier = '447367524129';

-- Verify unblock:
SELECT * FROM geo_blocked_identifiers
WHERE type = 'phone' AND identifier = '447367524129';
-- Should return 0 rows. users.phone is unchanged.
