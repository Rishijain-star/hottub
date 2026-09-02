<?php

namespace App\Services;

use App\Models\RegisteredUser;
use Illuminate\Http\Request;

class RegisteredUserTracker
{
    public function __construct(
        protected RegistrationSecurityService $registrationSecurity,
        protected GeoRestrictionService $geo,
    ) {}

    public function start(Request $request, array $payload): RegisteredUser
    {
        $signals = $this->signals($request);

        return RegisteredUser::create(array_merge($signals, [
            'status' => RegisteredUser::STATUS_STARTED,
            'role' => (string) ($payload['role'] ?? 'user'),
            'name' => $payload['name'] ?? null,
            'email' => isset($payload['email']) ? mb_strtolower(trim((string) $payload['email'])) : null,
            'phone' => $payload['phone'] ?? null,
            'postcode' => $payload['postcode'] ?? null,
            'meta' => $payload['meta'] ?? null,
        ]));
    }

    public function attachToSession(Request $request, RegisteredUser $record): void
    {
        if ($request->hasSession()) {
            $request->session()->put('registered_user_id', $record->id);
        }
    }

    public function current(Request $request): ?RegisteredUser
    {
        $id = $request->session()->get('registered_user_id');
        if (! is_numeric($id)) {
            return null;
        }

        return RegisteredUser::find((int) $id);
    }

    public function markEmailPending(RegisteredUser $record): void
    {
        $record->update(['status' => RegisteredUser::STATUS_EMAIL_PENDING]);
    }

    public function markEmailVerified(RegisteredUser $record): void
    {
        $record->update([
            'status' => RegisteredUser::STATUS_EMAIL_VERIFIED,
            'email_verified_at' => now(),
        ]);
    }

    public function recordSmsSent(RegisteredUser $record): void
    {
        $record->update([
            'status' => RegisteredUser::STATUS_SMS_SENT,
            'sms_sent_count' => (int) $record->sms_sent_count + 1,
            'last_sms_sent_at' => now(),
        ]);
    }

    public function markCompleted(RegisteredUser $record, int $userId): void
    {
        $record->update([
            'status' => RegisteredUser::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_user_id' => $userId,
        ]);
    }

    public function markBlocked(RegisteredUser $record, string $reason): void
    {
        $record->update([
            'status' => RegisteredUser::STATUS_BLOCKED,
            'block_reason' => $reason,
        ]);
    }

    /**
     * Block abusive clients before SMS is sent.
     */
    public function assertCanSendSms(Request $request): ?string
    {
        if ($this->geo->skipsChecksOnLocal()) {
            return null;
        }

        $identifierLock = app(OtpIdentifierLockService::class);
        if ($msg = $identifierLock->assertCanDeliverOtp($request)) {
            return $msg;
        }

        $signals = $this->signals($request);
        $dayStart = now()->startOfDay();

        $maxPerDevice = max(1, (int) config('registration.max_sms_per_device_per_day', 2));
        $maxPerHw = max(1, (int) config('registration.max_sms_per_hardware_per_day', 2));
        $maxPerPersistent = max(1, (int) config('registration.max_sms_per_persistent_id_per_day', 2));
        $maxPerIp = max(1, (int) config('registration.max_sms_per_ip_per_day', 5));

        $base = RegisteredUser::query()->where('created_at', '>=', $dayStart);

        if ($signals['device_id']) {
            $count = (clone $base)->where('device_id', $signals['device_id'])->sum('sms_sent_count');
            if ($count >= $maxPerDevice) {
                return 'Too many verification attempts from this device. Please try again tomorrow.';
            }
        }

        if ($signals['hardware_profile_hash']) {
            $count = (clone $base)->where('hardware_profile_hash', $signals['hardware_profile_hash'])->sum('sms_sent_count');
            if ($count >= $maxPerHw) {
                return 'Too many verification attempts from this device. Please try again tomorrow.';
            }
        }

        if ($signals['persistent_id']) {
            $count = (clone $base)->where('persistent_id', $signals['persistent_id'])->sum('sms_sent_count');
            if ($count >= $maxPerPersistent) {
                return 'Too many verification attempts from this device. Please try again tomorrow.';
            }
        }

        if ($signals['registration_ip']) {
            $count = (clone $base)->where('registration_ip', $signals['registration_ip'])->sum('sms_sent_count');
            if ($count >= $maxPerIp) {
                return 'Too many verification requests from this network. Please try again later.';
            }
        }

        $blocked = RegisteredUser::query()
            ->where('status', RegisteredUser::STATUS_BLOCKED)
            ->where(function ($q) use ($signals) {
                if ($signals['device_id']) {
                    $q->orWhere('device_id', $signals['device_id']);
                }
                if ($signals['hardware_profile_hash']) {
                    $q->orWhere('hardware_profile_hash', $signals['hardware_profile_hash']);
                }
                if ($signals['persistent_id']) {
                    $q->orWhere('persistent_id', $signals['persistent_id']);
                }
                if ($signals['registration_ip']) {
                    $q->orWhere('registration_ip', $signals['registration_ip']);
                }
            })
            ->exists();

        if ($blocked) {
            return 'Registration is not available from this device. Please contact support if you need help.';
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function signals(Request $request): array
    {
        $ua = (string) $request->userAgent();
        $hwRaw = trim((string) $request->input('client_hw_fp', ''));
        $fpRaw = trim((string) $request->input('client_fp', ''));

        return [
            'registration_ip' => $this->geo->clientIp($request),
            'session_id' => $request->hasSession() ? (string) $request->session()->getId() : null,
            'device_id' => $this->registrationSecurity->resolveDeviceId($request),
            'persistent_id' => $this->geo->persistentIdFromRequest($request)
                ?: trim((string) $request->input('client_pid', '')) ?: null,
            'hardware_profile_hash' => $hwRaw !== '' ? hash('sha256', $hwRaw) : $this->geo->hwProfileHashFromRequest($request),
            'fingerprint_hash' => $fpRaw !== '' && strlen($fpRaw) <= 512 ? hash('sha256', $fpRaw) : $this->geo->fingerprintHashFromRequest($request),
            'user_agent' => $ua !== '' ? $ua : null,
            'os_name' => $this->parseOsName($ua),
            'browser_name' => $this->parseBrowserName($ua),
            'platform' => $this->parsePlatform($ua),
        ];
    }

    private function parseOsName(string $ua): ?string
    {
        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS X'), str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => null,
        };
    }

    private function parseBrowserName(string $ua): ?string
    {
        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome/') => 'Safari',
            default => null,
        };
    }

    private function parsePlatform(string $ua): ?string
    {
        return match (true) {
            str_contains($ua, 'Mobile') => 'mobile',
            str_contains($ua, 'Tablet'), str_contains($ua, 'iPad') => 'tablet',
            $ua !== '' => 'desktop',
            default => null,
        };
    }
}
