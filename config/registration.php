<?php

return [
    'max_accounts_per_ip' => (int) env('REGISTRATION_MAX_ACCOUNTS_PER_IP', 3),
    'max_accounts_per_device' => (int) env('REGISTRATION_MAX_ACCOUNTS_PER_DEVICE', 2),
    'max_otp_requests_per_ip_hour' => (int) env('REGISTRATION_MAX_OTP_REQUESTS_PER_IP_HOUR', 10),
    'max_otp_verify_attempts' => (int) env('REGISTRATION_MAX_OTP_VERIFY_ATTEMPTS', 5),
    'device_cookie_name' => env('REGISTRATION_DEVICE_COOKIE', 'htb_did'),
    'device_cookie_minutes' => (int) env('REGISTRATION_DEVICE_COOKIE_MINUTES', 525600),
    'require_device_cookie' => (bool) env('REGISTRATION_REQUIRE_DEVICE_COOKIE', false),
    'max_sms_per_device_per_day' => (int) env('REGISTRATION_MAX_SMS_PER_DEVICE_DAY', 2),
    'max_sms_per_hardware_per_day' => (int) env('REGISTRATION_MAX_SMS_PER_HARDWARE_DAY', 2),
    'max_sms_per_persistent_id_per_day' => (int) env('REGISTRATION_MAX_SMS_PER_PERSISTENT_ID_DAY', 2),
    'max_sms_per_ip_per_day' => (int) env('REGISTRATION_MAX_SMS_PER_IP_DAY', 5),
    'require_math_captcha' => (bool) env('REGISTRATION_REQUIRE_MATH_CAPTCHA', true),
    'require_image_captcha' => env('REGISTRATION_REQUIRE_IMAGE_CAPTCHA') !== null
        ? (bool) env('REGISTRATION_REQUIRE_IMAGE_CAPTCHA')
        : null,
    'image_captcha_reuse_minutes' => (int) env('REGISTRATION_IMAGE_CAPTCHA_REUSE_MINUTES', 30),
];
