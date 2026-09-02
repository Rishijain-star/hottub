<?php

namespace App\Services;

use App\Mail\RegistrationEmailOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailOtpService
{
    private bool $lastSendWasSimulated = false;

    public function lastSendWasSimulated(): bool
    {
        return $this->lastSendWasSimulated;
    }

    /**
     * @return array{ok: bool, code?: string, error?: string}
     */
    public function sendRegistrationOtp(string $email, ?Request $request = null): array
    {
        $this->lastSendWasSimulated = false;
        $request = $request ?? request();

        try {
            app(EmailValidationService::class)->assertCanReceiveOtp($email);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ['ok' => false, 'error' => collect($e->errors())->flatten()->first() ?? 'Invalid email.'];
        }

        $limiter = app(EmailOtpRateLimitService::class);
        if (! $limiter->allowSend($email, $request)) {
            return ['ok' => false, 'error' => $limiter->lastReason() ?? 'Too many requests. Please try again later.'];
        }

        $code = (string) random_int(100000, 999999);
        $sent = $this->deliverOtp($email, $code);

        if (! $sent && app()->environment('local', 'testing')) {
            Log::info('Email OTP (dev/log): sent to ' . $this->maskEmail($email));
            $this->lastSendWasSimulated = true;
            $sent = true;
        }

        if (! $sent) {
            Log::warning('Email OTP delivery failed', ['email_hash' => hash('sha256', strtolower(trim($email)))]);

            return ['ok' => false, 'error' => 'Unable to send verification email right now. Please try again later.'];
        }

        $limiter->recordSend($email, $request);
        Log::info('Email OTP sent', [
            'email_hash' => hash('sha256', strtolower(trim($email))),
            'ip' => $request ? app(GeoRestrictionService::class)->clientIp($request) : null,
            'simulated' => $this->lastSendWasSimulated,
        ]);

        return ['ok' => true, 'code' => $code];
    }

    private function deliverOtp(string $email, string $code): bool
    {
        try {
            Mail::to($email)->send(new RegistrationEmailOtpMail($code));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Mail send exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $local = $parts[0];
        $masked = strlen($local) > 2 ? substr($local, 0, 1) . '***' . substr($local, -1) : '***';

        return $masked . '@' . $parts[1];
    }
}
