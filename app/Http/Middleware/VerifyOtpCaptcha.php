<?php

namespace App\Http\Middleware;

use App\Services\GeoRestrictionService;
use App\Services\ImageCaptchaService;
use App\Services\OtpAbuseBlockService;
use App\Services\OtpIdentifierLockService;
use App\Services\TurnstileService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOtpCaptcha
{
    public const SESSION_REGISTER_CAPTCHA_AT = 'registration_captcha_verified_at';

    public const SESSION_IMAGE_CAPTCHA_VERIFIED_AT = 'image_captcha_verified_at';

    public function __construct(
        protected TurnstileService $turnstile,
        protected GeoRestrictionService $geo,
        protected OtpAbuseBlockService $abuseBlock,
        protected ImageCaptchaService $imageCaptcha,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->geo->skipsChecksOnLocal()) {
            return $next($request);
        }

        $phone = is_string($request->input('phone')) ? $request->input('phone') : null;
        $identifierLock = app(OtpIdentifierLockService::class);
        if ($identifierLock->isLocked($request)) {
            return $this->fail($request, $identifierLock->lockMessage());
        }

        if ($this->abuseBlock->isPermanentlyBlocked($request)) {
            return $this->denyResponse($request);
        }

        if ($this->requiresImageCaptchaForRequest($request)) {
            if (! $this->imageCaptchaVerifiedRecently($request)
                && ! $this->imageCaptcha->verify($request, $request->input('image_captcha_code'))) {
                return $this->fail($request, 'Please enter the correct 6-digit code from the security image.');
            }

            $request->session()->put(self::SESSION_IMAGE_CAPTCHA_VERIFIED_AT, now()->timestamp);
        } elseif ($request->routeIs(
            'verify.phone.resend',
            'register.otp.resend',
            'register.email.otp.resend',
            'two-factor.send',
            'two-factor.resend'
        ) && $this->imageCaptchaVerifiedRecently($request)) {
            // Extend reuse window after Turnstile-only resend steps.
            $request->session()->put(self::SESSION_IMAGE_CAPTCHA_VERIFIED_AT, now()->timestamp);
        }

        $requireInProduction = app()->environment('production')
            && (bool) config('services.turnstile.enabled', true);

        if ($requireInProduction && ! $this->turnstile->isEnabled()) {
            $message = 'Security verification is not configured. OTP cannot be sent right now.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 503);
            }

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'cf-turnstile-response', 'image_captcha_code']))
                ->with('error', $message)
                ->with('show_toast', true);
        }

        if (! $this->turnstile->isEnabled()) {
            return $next($request);
        }

        $token = (string) $request->input('cf-turnstile-response', '');
        $ip = $this->geo->clientIp($request);

        if (! $this->turnstile->verify($token, $ip)) {
            $message = trim($token) === ''
                ? 'Cloudflare security check did not load. Please refresh the page, disable VPN/ad-blockers, and try again.'
                : 'Please complete the Cloudflare security check (traffic lights / images if shown).';

            if ($this->shouldBlockOnCaptchaFail($request) && trim($token) !== '') {
                $phone = is_string($request->input('phone')) ? $request->input('phone') : null;
                $this->abuseBlock->blockClient($request, 'captcha_fail', $phone);

                if ($this->abuseBlock->isPermanentlyBlocked($request)) {
                    return $this->denyResponse($request);
                }
            }

            return $this->fail($request, $message);
        }

        if ($request->routeIs('register.submit')) {
            $request->session()->put(self::SESSION_REGISTER_CAPTCHA_AT, now()->timestamp);
        }

        return $next($request);
    }

    private function requiresImageCaptcha(): bool
    {
        if (config('registration.require_image_captcha') !== null) {
            return (bool) config('registration.require_image_captcha');
        }

        return (bool) config('registration.require_math_captcha', true);
    }

    private function requiresImageCaptchaForRequest(Request $request): bool
    {
        if (! $this->requiresImageCaptcha()) {
            return false;
        }

        if ($request->routeIs(
            'verify.phone.resend',
            'register.otp.resend',
            'register.email.otp.resend',
            'two-factor.send',
            'two-factor.resend'
        )) {
            return false;
        }

        return true;
    }

    private function imageCaptchaVerifiedRecently(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $verifiedAt = $request->session()->get(self::SESSION_IMAGE_CAPTCHA_VERIFIED_AT);
        if (! is_numeric($verifiedAt)) {
            return false;
        }

        $minutes = max(5, (int) config('registration.image_captcha_reuse_minutes', 30));

        return now()->timestamp - (int) $verifiedAt <= ($minutes * 60);
    }

    private function shouldBlockOnCaptchaFail(Request $request): bool
    {
        if (! config('otp_limits.block_on_captcha_fail', true)) {
            return false;
        }

        return ! $request->routeIs('register.submit', 'login', 'register.send.otp');
    }

    private function fail(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 422);
        }

        return back()
            ->withInput($request->except(['password', 'password_confirmation', 'cf-turnstile-response', 'image_captcha_code']))
            ->with('error', $message)
            ->with('show_toast', true);
    }

    private function denyResponse(Request $request): Response
    {
        $message = $this->abuseBlock->userBlockMessage($request);

        return $this->fail($request, $message);
    }
}
