<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private const FIRETEXT_SEND_URL = 'https://www.firetext.co.uk/api/sendsms';

    private ?string $lastBlockReason = null;

    private bool $lastSendWasSimulated = false;

    public function lastSendWasSimulated(): bool
    {
        return $this->lastSendWasSimulated;
    }

    public function hasLiveProvider(): bool
    {
        return $this->hasFireText() || $this->hasTwilio();
    }

    public function lastBlockReason(): ?string
    {
        return $this->lastBlockReason;
    }

    public function wasRateLimited(): bool
    {
        if ($this->lastBlockReason === null) {
            return false;
        }

        return str_contains($this->lastBlockReason, 'Too many requests')
            || app(OtpIdentifierLockService::class)->isLockMessage($this->lastBlockReason);
    }

    public function sendVerificationCode(string $phone, string $code): bool
    {
        return $this->sendSmsMessage($phone, 'Your Hot Tub Buyer verification code is: ' . $code);
    }

    public function sendAdminTwoFactorCode(string $phone, string $code): bool
    {
        return $this->sendSmsMessage($phone, 'Your Hot Tub Buyer admin login code is: ' . $code);
    }

    public function isSupportedUkMobile(string $phone): bool
    {
        return $this->normalizePhoneFireText($phone) !== '';
    }

    public function sendFailureReason(string $phone): string
    {
        if ($this->lastBlockReason) {
            return $this->lastBlockReason;
        }

        if (! $this->isSupportedUkMobile($phone)) {
            return 'Invalid mobile number.';
        }

        if (! $this->hasLiveProvider()) {
            if (app()->environment('local', 'testing')) {
                return '';
            }

            return 'Unable to send verification code right now. Please try again later.';
        }

        return 'Unable to send SMS right now. Please try again later.';
    }

    private function sendSmsMessage(string $phone, string $msg): bool
    {
        $this->lastBlockReason = null;
        $this->lastSendWasSimulated = false;

        if (app(GeoRestrictionService::class)->isBlockedPhone($phone)) {
            $this->lastBlockReason = 'This mobile number is blocked. Please contact support.';

            return false;
        }

        $limiter = app(OtpRateLimitService::class);
        if (! $limiter->allowSend($phone)) {
            $this->lastBlockReason = $limiter->lastReason() ?? $limiter->tooManyMessage();
            Log::warning('OTP not sent — rate limit', ['phone_tail' => substr(preg_replace('/\D/', '', $phone) ?? '', -4)]);

            return false;
        }

        $sent = false;

        if ($this->hasFireText()) {
            if ($this->sendViaFireText($phone, $msg)) {
                $sent = true;
            } else {
                Log::warning('FireText SMS send did not succeed; falling back to log output.');
            }
        }

        if (! $sent && $this->hasTwilio()) {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            try {
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->messages->create($this->normalizePhoneTwilio($phone), [
                    'from' => $from,
                    'body' => $msg,
                ]);
                $sent = true;
            } catch (\Throwable $e) {
                Log::warning('SMS send failed: ' . $e->getMessage());
            }
        } elseif (! $sent && (config('services.twilio.sid') || config('services.twilio.token'))) {
            Log::warning('Twilio credentials set but twilio/sdk package may be missing; OTP logged only.');
        }

        if (! $sent && app()->environment('local', 'testing')) {
            Log::info('SMS (dev/log): ' . $msg . ' to ' . $phone);
            $this->lastSendWasSimulated = true;
            $sent = true;
        }

        if ($sent) {
            $limiter->recordSend($phone);
        }

        return $sent;
    }

    private function hasFireText(): bool
    {
        $key = config('services.firetext.api_key');
        $from = (string) config('services.firetext.from', '');
        $from = preg_replace('/[^A-Za-z0-9]/', '', $from);

        return (bool) ($key && strlen($from) >= 3 && strlen($from) <= 11);
    }

    private function hasTwilio(): bool
    {
        return (bool) (
            config('services.twilio.sid')
            && config('services.twilio.token')
            && config('services.twilio.from')
            && class_exists(\Twilio\Rest\Client::class)
        );
    }

    private function sendViaFireText(string $phone, string $message): bool
    {
        $from = (string) config('services.firetext.from', '');
        $from = preg_replace('/[^A-Za-z0-9]/', '', $from);
        $to = $this->normalizePhoneFireText($phone);
        $apiKey = (string) config('services.firetext.api_key');

        if ($to === '') {
            $this->lastBlockReason = 'Invalid UK mobile number format.';
            Log::warning('FireText SMS skipped: invalid UK destination format.', [
                'original_phone' => $phone,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(30)->asForm()->post(self::FIRETEXT_SEND_URL, [
                'apiKey' => $apiKey,
                'to' => $to,
                'from' => $from,
                'message' => $message,
            ]);

            $body = trim($response->body());
            if (str_starts_with($body, '0:')) {
                return true;
            }

            $this->lastBlockReason = $this->humanizeFireTextResponse($body);
            Log::warning('FireText SMS API response: ' . $body);
        } catch (\Throwable $e) {
            $this->lastBlockReason = 'SMS provider connection failed: ' . $e->getMessage();
            Log::warning('FireText SMS exception: ' . $e->getMessage());
        }

        return false;
    }

    private function normalizePhoneFireText(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '44')) {
            $uk = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $uk = '44' . substr($digits, 1);
        } else {
            return '';
        }

        if (! preg_match('/^447\d{9}$/', $uk)) {
            return '';
        }

        return $uk;
    }

    private function normalizePhoneTwilio(string $phone): string
    {
        $p = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($p, '0')) {
            $p = '+44' . substr($p, 1);
        }
        if (! str_starts_with($p, '+')) {
            $p = '+' . $p;
        }

        return $p;
    }

    private function humanizeFireTextResponse(string $body): string
    {
        if ($body === '') {
            return 'SMS provider returned an empty response. Check FireText API key and account credits.';
        }

        if (preg_match('/^(\d+):(.+)$/s', $body, $m)) {
            return 'SMS provider error (' . trim($m[1]) . '): ' . trim($m[2]);
        }

        return 'SMS provider error: ' . $body;
    }
}
