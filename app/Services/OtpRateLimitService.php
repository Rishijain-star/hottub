<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpRateLimitService
{
    private ?string $lastReason = null;

    public function lastReason(): ?string
    {
        return $this->lastReason;
    }

    public function tooManyMessage(): string
    {
        return 'Too many requests. Please try again later.';
    }

    public function isHoneypotTripped(?Request $request = null): bool
    {
        $request = $request ?? request();
        if (! $request) {
            return false;
        }

        $trap = trim((string) $request->input(config('services.turnstile.honeypot_field', 'company_website'), ''));

        return $trap !== '';
    }

    /**
     * Returns false when OTP must NOT be sent (before FireText API call).
     */
    public function allowSend(string $phone, ?Request $request = null): bool
    {
        $this->lastReason = null;
        $request = $request ?? request();

        if (app(GeoRestrictionService::class)->skipsChecksOnLocal()) {
            return true;
        }

        if ($request) {
            $identifierLock = app(OtpIdentifierLockService::class);
            if ($msg = $identifierLock->assertCanDeliverOtp($request, $phone)) {
                $this->lastReason = $msg;

                return false;
            }

            app(OtpAbuseBlockService::class)->trackAttempt($request, $phone);
            if (app(OtpAbuseBlockService::class)->isBlocked($request)) {
                $this->lastReason = app(OtpIdentifierLockService::class)->isLocked($request)
                    ? app(OtpIdentifierLockService::class)->lockMessage()
                    : $this->tooManyMessage();

                return false;
            }
        }

        if ($this->isHoneypotTripped($request)) {
            $this->lastReason = $this->tooManyMessage();
            Log::warning('OTP blocked: honeypot');
            if (config('otp_limits.block_on_honeypot', true) && $request) {
                app(OtpAbuseBlockService::class)->blockClient($request, 'otp_honeypot', $phone);
            }

            return false;
        }

        $day = now()->format('Y-m-d');
        $dailyLimit = max(1, (int) config('otp_limits.daily_platform_limit', 20));
        $dailyKey = 'otp_sent_daily:' . $day;
        $lock = Cache::lock('otp_sent_daily_lock:' . $day, 10);
        try {
            $lock->block(5);
            if ((int) Cache::get($dailyKey, 0) >= $dailyLimit) {
                $this->lastReason = $this->tooManyMessage();
                Log::warning('OTP blocked: daily platform limit', ['count' => Cache::get($dailyKey)]);

                return false;
            }
        } finally {
            optional($lock)->release();
        }

        $deviceId = $this->deviceId($request);
        if ($deviceId) {
            $deviceDayKey = 'otp_sent_device:' . $deviceId . ':' . $day;
            $deviceLimit = max(1, (int) config('otp_limits.daily_device_limit', 3));
            if ((int) Cache::get($deviceDayKey, 0) >= $deviceLimit) {
                $this->lastReason = $this->tooManyMessage();
                Log::warning('OTP blocked: device daily limit', ['device' => substr($deviceId, 0, 8)]);
                if (config('otp_limits.block_on_device_limit', true) && $request) {
                    app(OtpAbuseBlockService::class)->blockClient($request, 'otp_device_limit', $phone);
                }

                return false;
            }

            $cooldownKey = 'otp_device_cooldown:' . $deviceId;
            $minGap = max(10, (int) config('otp_limits.min_seconds_between_device', 60));
            if (Cache::has($cooldownKey)) {
                $this->lastReason = $this->tooManyMessage();

                return false;
            }
        }

        $fingerprint = $this->fingerprintHash($request);
        if ($fingerprint) {
            $fpKey = 'otp_sent_fp:' . $fingerprint . ':' . $day;
            $deviceLimit = max(1, (int) config('otp_limits.daily_device_limit', 3));
            if ((int) Cache::get($fpKey, 0) >= $deviceLimit) {
                $this->lastReason = $this->tooManyMessage();
                Log::warning('OTP blocked: fingerprint daily limit');
                if (config('otp_limits.block_on_device_limit', true) && $request) {
                    app(OtpAbuseBlockService::class)->blockClient($request, 'otp_fingerprint_limit', $phone);
                }

                return false;
            }
        }

        $ip = $this->clientIp($request);
        if ($ip) {
            $ipHourKey = 'otp_sent_ip:' . $ip . ':' . now()->format('Y-m-d-H');
            $ipLimit = max(1, (int) config('otp_limits.hourly_ip_limit', 8));
            if ((int) Cache::get($ipHourKey, 0) >= $ipLimit) {
                $this->lastReason = $this->tooManyMessage();
                Log::warning('OTP blocked: IP hourly limit', ['ip' => $ip]);

                return false;
            }
        }

        $normalizedPhone = $this->normalizePhoneKey($phone);
        if ($normalizedPhone !== '') {
            $phoneHourKey = 'otp_sent_phone:' . $normalizedPhone . ':' . now()->format('Y-m-d-H');
            $phoneLimit = max(1, (int) config('otp_limits.hourly_phone_limit', 3));
            if ((int) Cache::get($phoneHourKey, 0) >= $phoneLimit) {
                $this->lastReason = $this->tooManyMessage();
                Log::warning('OTP blocked: phone hourly limit');

                return false;
            }
        }

        return true;
    }

    public function recordSend(string $phone, ?Request $request = null): void
    {
        if (app(GeoRestrictionService::class)->skipsChecksOnLocal()) {
            return;
        }

        $request = $request ?? request();
        $day = now()->format('Y-m-d');
        $hour = now()->format('Y-m-d-H');
        $deviceLimit = max(1, (int) config('otp_limits.daily_device_limit', 3));

        if ($request) {
            app(OtpAbuseBlockService::class)->trackAttempt($request, $phone);
        }

        Cache::increment('otp_sent_daily:' . $day);
        Cache::put('otp_sent_daily:' . $day, (int) Cache::get('otp_sent_daily:' . $day, 0), now()->endOfDay());

        $deviceId = $this->deviceId($request);
        if ($deviceId) {
            $deviceDayKey = 'otp_sent_device:' . $deviceId . ':' . $day;
            Cache::increment($deviceDayKey);
            $deviceCount = (int) Cache::get($deviceDayKey, 0);
            Cache::put($deviceDayKey, $deviceCount, now()->endOfDay());

            $minGap = max(10, (int) config('otp_limits.min_seconds_between_device', 60));
            Cache::put('otp_device_cooldown:' . $deviceId, true, now()->addSeconds($minGap));

            if ($request && $deviceCount >= $deviceLimit && config('otp_limits.block_on_device_limit', true)) {
                app(OtpAbuseBlockService::class)->blockClient($request, 'otp_device_limit', $phone);
            }
        }

        $fingerprint = $this->fingerprintHash($request);
        if ($fingerprint) {
            $fpKey = 'otp_sent_fp:' . $fingerprint . ':' . $day;
            Cache::increment($fpKey);
            $fpCount = (int) Cache::get($fpKey, 0);
            Cache::put($fpKey, $fpCount, now()->endOfDay());

            if ($request && $fpCount >= $deviceLimit && config('otp_limits.block_on_device_limit', true)) {
                app(OtpAbuseBlockService::class)->blockClient($request, 'otp_fingerprint_limit', $phone);
            }
        }

        $ip = $this->clientIp($request);
        if ($ip) {
            $ipHourKey = 'otp_sent_ip:' . $ip . ':' . $hour;
            Cache::increment($ipHourKey);
            Cache::put($ipHourKey, (int) Cache::get($ipHourKey, 0), now()->addHour());
        }

        $normalizedPhone = $this->normalizePhoneKey($phone);
        if ($normalizedPhone !== '') {
            $phoneHourKey = 'otp_sent_phone:' . $normalizedPhone . ':' . $hour;
            Cache::increment($phoneHourKey);
            Cache::put($phoneHourKey, (int) Cache::get($phoneHourKey, 0), now()->addHour());
        }

        if ($request) {
            app(OtpIdentifierLockService::class)->recordOtpDelivery($request, $phone);
        }
    }

    private function deviceId(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return app(RegistrationSecurityService::class)->resolveDeviceId($request);
    }

    private function fingerprintHash(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        $raw = trim((string) $request->input('client_fp', ''));
        if ($raw === '' || strlen($raw) > 512) {
            return null;
        }

        return hash('sha256', $raw);
    }

    private function clientIp(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return app(GeoRestrictionService::class)->clientIp($request);
    }

    private function normalizePhoneKey(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return $digits !== '' ? $digits : '';
    }
}
