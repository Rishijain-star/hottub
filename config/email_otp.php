<?php

return [
    'otp_expiry_minutes' => (int) env('EMAIL_OTP_EXPIRY_MINUTES', 10),
    'resend_cooldown_seconds' => (int) env('EMAIL_OTP_RESEND_COOLDOWN_SECONDS', 60),
    'max_verify_attempts' => (int) env('EMAIL_OTP_MAX_VERIFY_ATTEMPTS', 5),
    'max_requests_per_email_per_day' => (int) env('EMAIL_OTP_MAX_PER_EMAIL_DAY', 3),
    'max_requests_per_email_per_24h' => (int) env('EMAIL_OTP_MAX_PER_EMAIL_24H', 5),
    'max_requests_per_device_per_24h' => (int) env('EMAIL_OTP_MAX_PER_DEVICE_24H', 3),
    'max_requests_per_ip_per_24h' => (int) env('EMAIL_OTP_MAX_PER_IP_24H', 5),
    'skip_mx_on_local' => (bool) env('EMAIL_OTP_SKIP_MX_ON_LOCAL', true),
];
