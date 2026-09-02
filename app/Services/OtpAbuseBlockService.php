<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OtpAbuseBlockService
{
    public function __construct(
        protected GeoRestrictionService $geo,
        protected OtpIdentityLinker $linker,
    ) {}

    /**
     * Permanently block this client and every linked browser, IP, phone, and hardware profile.
     */
    public function blockClient(Request $request, string $reason, ?string $phone = null): void
    {
        if (app(GeoRestrictionService::class)->skipsChecksOnLocal()) {
            Log::info('OTP abuse block skipped on local', ['reason' => $reason]);

            return;
        }
        $bundle = $this->linker->collectFromRequest($request, $phone);
        $this->linker->record($request, $bundle);
        $expanded = $this->linker->expandBundle($bundle);

        $ip = $this->geo->clientIp($request);
        $blocked = $this->geo->persistIdentifierBundle($expanded, $reason, $ip);

        if ($request->hasSession()) {
            $request->session()->put(GeoRestrictionService::SESSION_DENIED, true);
        }

        Log::warning('OTP abuse — full device/network block', [
            'reason' => $reason,
            'ip' => $ip,
            'blocked_count' => $blocked,
        ]);
    }

    public function trackAttempt(Request $request, ?string $phone = null): void
    {
        $bundle = $this->linker->collectFromRequest($request, $phone);
        $this->linker->record($request, $bundle);
    }

    public function isBlocked(Request $request): bool
    {
        return $this->isPermanentlyBlocked($request)
            || app(OtpIdentifierLockService::class)->isLocked($request);
    }

    public function isPermanentlyBlocked(Request $request): bool
    {
        if ($this->geo->skipsChecksOnLocal()) {
            return false;
        }

        return $this->geo->isPersistentlyBlocked($request);
    }

    public function userBlockMessage(Request $request): string
    {
        $lock = app(OtpIdentifierLockService::class);
        if ($lock->isLocked($request)) {
            return $lock->lockMessage();
        }

        return (string) config(
            'otp_limits.abuse_block_message',
            'You have tried multiple times. Please come back after 24 hours.'
        );
    }
}
