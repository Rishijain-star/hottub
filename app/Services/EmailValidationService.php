<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class EmailValidationService
{
    public function assertCanReceiveOtp(string $email): void
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => 'Please enter a valid email address.',
            ]);
        }

        if ($this->isDisposable($email)) {
            throw ValidationException::withMessages([
                'email' => 'Please use a valid permanent email address.',
            ]);
        }

        if (! $this->domainHasMx($email)) {
            throw ValidationException::withMessages([
                'email' => 'Please enter a valid email address.',
            ]);
        }
    }

    public function isDisposable(string $email): bool
    {
        $domain = $this->domainFromEmail($email);
        if ($domain === '') {
            return true;
        }

        return in_array($domain, config('disposable_email_domains', []), true);
    }

    public function domainHasMx(string $email): bool
    {
        if (config('email_otp.skip_mx_on_local', true)
            && app()->environment('local', 'testing')) {
            return true;
        }

        $domain = $this->domainFromEmail($email);
        if ($domain === '') {
            return false;
        }

        $cacheKey = 'email_mx_ok:' . $domain;

        return (bool) Cache::remember($cacheKey, now()->addHours(24), function () use ($domain) {
            if (! function_exists('checkdnsrr')) {
                return true;
            }

            if (checkdnsrr($domain, 'MX')) {
                return true;
            }

            return checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA');
        });
    }

    private function domainFromEmail(string $email): string
    {
        $parts = explode('@', strtolower(trim($email)));

        return count($parts) === 2 ? $parts[1] : '';
    }
}
