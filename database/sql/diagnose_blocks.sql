-- Step 1: See what blocks are still in the database
SELECT reason, type, COUNT(*) AS total
FROM geo_blocked_identifiers
GROUP BY reason, type
ORDER BY total DESC;

-- Step 2: See ALL remaining rows (if login still blocked, your device may be here)
SELECT id, type, LEFT(identifier, 20) AS identifier_start, reason, created_at
FROM geo_blocked_identifiers
ORDER BY created_at DESC
LIMIT 50;

-- Step 3 (ONLY if login still blocked after code upload): remove OTP abuse blocks too
-- WARNING: this clears ALL abuse blocks, not just region blocks
-- DELETE FROM geo_blocked_identifiers;
