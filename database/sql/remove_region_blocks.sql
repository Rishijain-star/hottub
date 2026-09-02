-- Run in phpMyAdmin (SQL tab) to unblock users blocked by country/region restriction.
-- Does NOT remove OTP abuse blocks (otp_* reasons).

DELETE FROM geo_blocked_identifiers
WHERE reason LIKE 'country_%'
   OR reason IN (
        'stored_block',
        'register_gps',
        'gps_pk',
        'pk_phone'
    );

-- Optional: see remaining blocks (OTP abuse only should remain)
-- SELECT id, type, identifier, reason, created_at FROM geo_blocked_identifiers ORDER BY created_at DESC;
