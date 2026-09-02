<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationSecurityService
{
    private const SESSION_DEVICE_KEY = 'registration_device_id';

    public function deviceId(Request $request): ?string
    {
        $id = $request->cookie(config('registration.device_cookie_name', 'htb_did'));

        return is_string($id) && $id !== '' ? $id : null;
    }

    /**
     * Cookie when available; otherwise a stable ID in the registration session.
     */
    public function resolveDeviceId(Request $request): ?string
    {
        $fromCookie = $this->deviceId($request);
        if ($fromCookie) {
            return $fromCookie;
        }

        if (! $request->hasSession()) {
            return null;
        }

        $existing = $request->session()->get(self::SESSION_DEVICE_KEY);
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $generated = (string) Str::uuid();
        $request->session()->put(self::SESSION_DEVICE_KEY, $generated);

        return $generated;
    }

    /**
     * @throws ValidationException
     */
    public function assertCanRequestOtp(Request $request): void
    {
        $this->throttleOtpRequests($request);
        $this->assertRegistrationLimits($request);
    }

    /**
     * @throws ValidationException
     */
    public function assertCanCreateAccount(Request $request): void
    {
        $this->assertRegistrationLimits($request);
    }

    /**
     * @throws ValidationException
     */
    private function assertRegistrationLimits(Request $request): void
    {
        $ip = (string) $request->ip();
        $deviceId = $this->resolveDeviceId($request);
        $maxIp = max(1, (int) config('registration.max_accounts_per_ip', 3));
        $maxDevice = max(1, (int) config('registration.max_accounts_per_device', 2));

        if ($ip !== '' && User::query()->where('registration_ip', $ip)->count() >= $maxIp) {
            throw ValidationException::withMessages([
                'phone' => 'Registration is not available from this network. Please contact support if you need help.',
            ]);
        }

        if ($deviceId && User::query()->where('registration_device_id', $deviceId)->count() >= $maxDevice) {
            throw ValidationException::withMessages([
                'phone' => 'The maximum number of accounts for this device has been reached.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function throttleOtpRequests(Request $request): void
    {
        if (app(GeoRestrictionService::class)->skipsChecksOnLocal()) {
            return;
        }

        $ip = (string) $request->ip();
        if ($ip === '') {
            return;
        }

        $key = 'registration_otp_ip:' . $ip;
        $maxAttempts = max(1, (int) config('registration.max_otp_requests_per_ip_hour', 10));

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'phone' => 'Too many verification requests. Please try again later.',
            ]);
        }

        RateLimiter::hit($key, 3600);
    }

    public function registrationMeta(Request $request): array
    {
        return [
            'registration_ip' => (string) $request->ip(),
            'registration_device_id' => $this->resolveDeviceId($request),
        ];
    }
}
