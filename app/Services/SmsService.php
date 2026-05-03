<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private const FIRETEXT_SEND_URL = 'https://www.firetext.co.uk/api/sendsms';

    public function hasLiveProvider(): bool
    {
        return $this->hasFireText() || $this->hasTwilio();
    }

    public function sendVerificationCode(string $phone, string $code): bool
    {
        $msg = 'Your Hot Tub Buyer verification code is: ' . $code;

        if ($this->hasFireText()) {
            if ($this->sendViaFireText($phone, $msg)) {
                return true;
            }
            Log::warning('FireText SMS send did not succeed; falling back to log output.');
        }

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if ($this->hasTwilio()) {
            try {
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->messages->create($this->normalizePhoneTwilio($phone), [
                    'from' => $from,
                    'body' => $msg,
                ]);

                return true;
            } catch (\Throwable $e) {
                Log::warning('SMS send failed: ' . $e->getMessage());
            }
        } elseif ($sid || $token) {
            Log::warning('Twilio credentials set but twilio/sdk package may be missing; OTP logged only.');
        }

        if (app()->environment('local', 'testing')) {
            Log::info('SMS (dev/log): ' . $msg . ' to ' . $phone);
            return true;
        }

        return false;
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
            Log::warning('FireText SMS skipped: invalid UK destination format.', [
                'original_phone' => $phone,
                'normalized_to' => $to,
            ]);
            return false;
        }

        Log::info('FireText SMS request', [
            'method' => 'POST',
            'url' => self::FIRETEXT_SEND_URL,
            'to' => $to,
            'from' => $from,
            'api_key_tail' => substr($apiKey, -6),
        ]);

        try {
            $response = Http::timeout(30)->asForm()->post(self::FIRETEXT_SEND_URL, [
                'apiKey' => $apiKey,
                'to' => $to,
                'from' => $from,
                'message' => $message,
            ]);

            $body = trim($response->body());
            Log::info('FireText SMS response', [
                'http_status' => $response->status(),
                'body' => $body,
            ]);
            if (str_starts_with($body, '0:')) {
                return true;
            }

            Log::warning('FireText SMS API response: ' . $body);
        } catch (\Throwable $e) {
            Log::warning('FireText SMS exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * FireText expects digits only, no "+". UK numbers may be 07… or 447….
     *
     * @see https://firetext.co.uk/docs
     */
    private function normalizePhoneFireText(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        // FireText expects UK destination numbers as 07... or 447...
        // We normalize to international 447... (no + sign) for consistency.
        if (str_starts_with($digits, '44')) {
            $uk = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $uk = '44' . substr($digits, 1);
        } else {
            return '';
        }

        // UK mobile should be 447 + 9 digits (12 total digits).
        if (!preg_match('/^447\d{9}$/', $uk)) {
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
        if (!str_starts_with($p, '+')) {
            $p = '+' . $p;
        }

        return $p;
    }
}
