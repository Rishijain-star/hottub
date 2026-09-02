<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EmailOtpRateLimitService
{
    private ?string $lastReason = null;

    public function lastReason(): ?string
    {
        return $this->lastReason;
    }

    public function allowSend(string $email, ?Request $request = null): bool
    {
        $this->lastReason = null;

        if (app(GeoRestrictionService::class)->skipsChecksOnLocal()) {
            return true;
        }

        $request = $request ?? request();
        $lock = app(OtpIdentifierLockService::class);
        if ($msg = $lock->assertCanDeliverOtp($request)) {
            $this->lastReason = $msg;

            return false;
        }

        $normalized = $this->normalizeEmail($email);

        if ($normalized === '') {
            $this->lastReason = 'Please enter a valid email address.';

            return false;
        }

        $cooldownKey = 'email_otp_cooldown:' . $normalized;
        if (Cache::has($cooldownKey)) {
            $this->lastReason = 'Please wait before requesting another code.';

            return false;
        }

        $day = now()->format('Y-m-d');
        $dailyKey = 'email_otp_daily:' . $normalized . ':' . $day;
        $dailyLimit = max(1, (int) config('email_otp.max_requests_per_email_per_day', 3));
        if ((int) Cache::get($dailyKey, 0) >= $dailyLimit) {
            $this->lastReason = 'You have reached the maximum verification attempts. Please try again tomorrow.';
            $this->logViolation('email_daily', $email, $request);

            return false;
        }

        $rollingKey = 'email_otp_24h:' . $normalized;
        $rollingLimit = max(1, (int) config('email_otp.max_requests_per_email_per_24h', 5));
        if (RateLimiter::tooManyAttempts($rollingKey, $rollingLimit)) {
            $this->lastReason = 'You have reached the maximum verification attempts. Please try again tomorrow.';
            $this->logViolation('email_24h', $email, $request);

            return false;
        }

        $deviceId = $request ? app(RegistrationSecurityService::class)->resolveDeviceId($request) : null;
        if ($deviceId) {
            $deviceKey = 'email_otp_device:' . $deviceId . ':' . $day;
            $deviceLimit = max(1, (int) config('email_otp.max_requests_per_device_per_24h', 3));
            if ((int) Cache::get($deviceKey, 0) >= $deviceLimit) {
                $this->lastReason = 'Too many verification attempts from this device.';
                $this->logViolation('device', $email, $request);

                return false;
            }
        }

        $ip = $request ? app(GeoRestrictionService::class)->clientIp($request) : null;
        if ($ip) {
            $ipKey = 'email_otp_ip:' . $ip . ':' . $day;
            $ipLimit = max(1, (int) config('email_otp.max_requests_per_ip_per_24h', 5));
            if ((int) Cache::get($ipKey, 0) >= $ipLimit) {
                $this->lastReason = 'Too many requests. Please try again later.';
                $this->logViolation('ip', $email, $request);

                return false;
            }
        }

        return true;
    }

    public function recordSend(string $email, ?Request $request = null): void
    {
        if (app(GeoRestrictionService::class)->skipsChecksOnLocal()) {
            return;
        }

        $request = $request ?? request();
        $normalized = $this->normalizeEmail($email);
        if ($normalized === '') {
            return;
        }

        $day = now()->format('Y-m-d');
        $dailyKey = 'email_otp_daily:' . $normalized . ':' . $day;
        Cache::increment($dailyKey);
        Cache::put($dailyKey, (int) Cache::get($dailyKey, 0), now()->endOfDay());

        $rollingKey = 'email_otp_24h:' . $normalized;
        RateLimiter::hit($rollingKey, 86400);

        $cooldown = max(10, (int) config('email_otp.resend_cooldown_seconds', 60));
        Cache::put('email_otp_cooldown:' . $normalized, true, now()->addSeconds($cooldown));

        $deviceId = $request ? app(RegistrationSecurityService::class)->resolveDeviceId($request) : null;
        if ($deviceId) {
            $deviceKey = 'email_otp_device:' . $deviceId . ':' . $day;
            Cache::increment($deviceKey);
            Cache::put($deviceKey, (int) Cache::get($deviceKey, 0), now()->endOfDay());
        }

        $ip = $request ? app(GeoRestrictionService::class)->clientIp($request) : null;
        if ($ip) {
            $ipKey = 'email_otp_ip:' . $ip . ':' . $day;
            Cache::increment($ipKey);
            Cache::put($ipKey, (int) Cache::get($ipKey, 0), now()->endOfDay());
        }

        if ($request) {
            app(OtpIdentifierLockService::class)->recordOtpDelivery($request);
        }
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function logViolation(string $type, string $email, ?Request $request): void
    {
        Log::warning('Email OTP rate limit', [
            'type' => $type,
            'email_hash' => hash('sha256', strtolower(trim($email))),
            'ip' => $request ? app(GeoRestrictionService::class)->clientIp($request) : null,
            'device' => $request ? substr((string) app(RegistrationSecurityService::class)->resolveDeviceId($request), 0, 8) : null,
        ]);
    }
}
