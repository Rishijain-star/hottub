<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    public function isEnabled(): bool
    {
        return (bool) (
            config('services.turnstile.enabled', true)
            && config('services.turnstile.site_key')
            && config('services.turnstile.secret_key')
        );
    }

    public function siteKey(): ?string
    {
        $key = config('services.turnstile.site_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (! $this->isEnabled()) {
            return app()->environment('local', 'testing');
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        try {
            $response = Http::timeout(8)->asForm()->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                array_filter([
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ])
            );

            if (! $response->ok()) {
                return false;
            }

            return (bool) $response->json('success');
        } catch (\Throwable $e) {
            Log::warning('Turnstile verify failed', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
