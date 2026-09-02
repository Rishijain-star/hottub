<?php

return [
    'daily_platform_limit' => (int) env('OTP_DAILY_PLATFORM_LIMIT', 20),
    'daily_device_limit' => (int) env('OTP_DAILY_DEVICE_LIMIT', 3),
    'hourly_ip_limit' => (int) env('OTP_HOURLY_IP_LIMIT', 8),
    'hourly_phone_limit' => (int) env('OTP_HOURLY_PHONE_LIMIT', 3),
    'min_seconds_between_device' => (int) env('OTP_MIN_SECONDS_BETWEEN_DEVICE', 60),
    'block_on_device_limit' => (bool) env('OTP_BLOCK_ON_DEVICE_LIMIT', true),
    'block_on_captcha_fail' => (bool) env('OTP_BLOCK_ON_CAPTCHA_FAIL', true),
    'block_on_honeypot' => (bool) env('OTP_BLOCK_ON_HONEYPOT', true),
    'hardware_window_hours' => (int) env('OTP_HARDWARE_WINDOW_HOURS', 5),
    'hardware_max_attempts' => (int) env('OTP_HARDWARE_MAX_ATTEMPTS', 5),
    'hardware_lock_hours' => (int) env('OTP_HARDWARE_LOCK_HOURS', 24),
    'hardware_lock_message' => env('OTP_HARDWARE_LOCK_MESSAGE', 'You have tried multiple times. Please come back after 24 hours.'),
    'abuse_block_message' => env('OTP_ABUSE_BLOCK_MESSAGE', 'You have tried multiple times. Please come back after 24 hours.'),
];
