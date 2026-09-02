<?php

namespace App\Services;

use App\Models\GeoBlockedIdentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Cookie;

class GeoRestrictionService
{
    public const SESSION_DENIED = 'region_access_denied';

    public function enabled(): bool
    {
        // Region / country blocking removed site-wide (OTP abuse uses separate checks).
        return false;
    }

    public function skipsChecksOnLocal(): bool
    {
        return config('geo_restrictions.skip_local', true)
            && app()->environment('local')
            && ! config('geo_restrictions.test_block', false);
    }

    /**
     * @return list<string>
     */
    public function blockedCountries(): array
    {
        $codes = config('geo_restrictions.blocked_countries', ['PK']);

        return array_values(array_filter(array_map('strtoupper', $codes)));
    }

    public function isAccessDenied(Request $request): bool
    {
        // Region restriction removed — site is available in all countries.
        return false;
    }

    public function persistBlock(Request $request, string $reason): void
    {
        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_DENIED, true);
        }

        $bundle = [
            'devices' => array_values(array_filter([$this->deviceIdFromRequest($request)])),
            'fingerprints' => array_values(array_filter([$this->fingerprintHashFromRequest($request)])),
            'ips' => array_values(array_filter([$this->clientIp($request)])),
            'phones' => [],
            'hw_profiles' => array_values(array_filter([$this->hwProfileHashFromRequest($request)])),
            'persistent_ids' => array_values(array_filter([$this->persistentIdFromRequest($request)])),
        ];

        $this->persistIdentifierBundle($bundle, $reason, $this->clientIp($request));
    }

    /**
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $bundle
     */
    public function persistIdentifierBundle(array $bundle, string $reason, ?string $lastIp = null): int
    {
        if (! Schema::hasTable('geo_blocked_identifiers')) {
            return 0;
        }

        $count = 0;

        try {
            foreach ($bundle['devices'] as $id) {
                $this->persistOne('device', $id, $reason, $lastIp);
                $count++;
            }
            foreach ($bundle['fingerprints'] as $id) {
                $this->persistOne('fingerprint', $id, $reason, $lastIp);
                $count++;
            }
            foreach ($bundle['ips'] as $ip) {
                if ($ip && ! $this->isPrivateIp($ip)) {
                    $this->persistOne('ip', $ip, $reason, $ip);
                    $count++;
                }
            }
            foreach ($bundle['phones'] as $phone) {
                $this->persistOne('phone', $phone, $reason, $lastIp);
                $count++;
            }
            foreach ($bundle['hw_profiles'] as $id) {
                $this->persistOne('hw_profile', $id, $reason, $lastIp);
                $count++;
            }
            foreach ($bundle['persistent_ids'] as $id) {
                $this->persistOne('persistent_id', $id, $reason, $lastIp);
                $count++;
            }
        } catch (\Throwable $e) {
            Log::warning('Geo block persist failed', ['message' => $e->getMessage()]);
        }

        return $count;
    }

    private function persistOne(string $type, string $identifier, string $reason, ?string $lastIp): void
    {
        GeoBlockedIdentifier::query()->updateOrCreate(
            ['type' => $type, 'identifier' => $identifier],
            ['reason' => $reason, 'last_ip' => $lastIp]
        );
    }

    public function markDenied(): void
    {
        if (request()->hasSession()) {
            request()->session()->put(self::SESSION_DENIED, true);
        }
    }

    public function blockCookie(): Cookie
    {
        $name = (string) config('geo_restrictions.block_cookie_name', 'htb_region_blocked');
        $minutes = max(60, (int) config('geo_restrictions.block_cookie_minutes', 525600));
        $secure = config('session.secure');
        if ($secure === null) {
            $secure = request()->isSecure();
        }

        return new Cookie(
            $name,
            '1',
            $minutes,
            '/',
            config('session.domain'),
            (bool) $secure,
            true,
            false,
            Cookie::SAMESITE_LAX
        );
    }

    public function hasBlockCookie(Request $request): bool
    {
        return $request->cookie((string) config('geo_restrictions.block_cookie_name', 'htb_region_blocked')) === '1';
    }

    public function isPersistentlyBlocked(Request $request): bool
    {
        if (! Schema::hasTable('geo_blocked_identifiers')) {
            return false;
        }

        if ($this->identifierBlocked('device', $this->deviceIdFromRequest($request))) {
            return true;
        }

        if ($this->identifierBlocked('fingerprint', $this->fingerprintHashFromRequest($request))) {
            return true;
        }

        if ($this->identifierBlocked('hw_profile', $this->hwProfileHashFromRequest($request))) {
            return true;
        }

        if ($this->identifierBlocked('persistent_id', $this->persistentIdFromRequest($request))) {
            return true;
        }

        $ip = $this->clientIp($request);
        if ($ip && ! $this->isPrivateIp($ip) && $this->identifierBlocked('ip', $ip)) {
            return true;
        }

        $phone = $this->phoneFromRequest($request);
        if ($phone && $this->identifierBlocked('phone', $phone)) {
            return true;
        }

        return false;
    }

    private function identifierBlocked(string $type, ?string $identifier): bool
    {
        if ($identifier === null || $identifier === '') {
            return false;
        }

        return GeoBlockedIdentifier::query()
            ->where('type', $type)
            ->where('identifier', $identifier)
            ->exists();
    }

    public function isBlockedPhone(?string $phone): bool
    {
        if ($phone === null || trim($phone) === '') {
            return false;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return $digits !== '' && $this->identifierBlocked('phone', $digits);
    }

    public function isBlockedPostcode(?string $postcode): bool
    {
        return false;
    }

    public function isBlockedCountryCode(?string $code): bool
    {
        return false;
    }

    public function isBlockedCoordinates(?float $lat, ?float $lng): bool
    {
        return false;
    }

    public function genericDenyMessage(): string
    {
        return 'This website is not available in your region.';
    }

    public function clientIp(Request $request): ?string
    {
        $candidates = [];

        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP'] as $header) {
            $value = $request->header($header);
            if (is_string($value) && $value !== '') {
                $candidates[] = trim(explode(',', $value)[0]);
            }
        }

        $forwarded = $request->header('X-Forwarded-For');
        if (is_string($forwarded) && $forwarded !== '') {
            foreach (explode(',', $forwarded) as $part) {
                $candidates[] = trim($part);
            }
        }

        $candidates[] = $request->ip();

        foreach ($candidates as $ip) {
            if ($ip !== '' && ! $this->isPrivateIp($ip)) {
                return $ip;
            }
        }

        return $request->ip() ?: null;
    }

    private function requestMatchesCountry(Request $request, string $countryCode): bool
    {
        $countryCode = strtoupper($countryCode);

        $headerCountry = $request->header('CF-IPCountry') ?? $request->header('X-Country-Code');
        if (is_string($headerCountry) && strtoupper($headerCountry) === $countryCode) {
            return true;
        }

        $sessionCountry = $request->hasSession() ? $request->session()->get('geo_country') : null;
        if (is_string($sessionCountry) && strtoupper($sessionCountry) === $countryCode) {
            return true;
        }

        if ($request->hasSession() && $request->session()->has('geo_lat') && $request->session()->has('geo_lng')) {
            if ($this->isBlockedCoordinates((float) $request->session()->get('geo_lat'), (float) $request->session()->get('geo_lng'))) {
                return true;
            }
        }

        $ip = $this->clientIp($request);
        $ipInfo = $this->lookupIp($ip);
        if ($ipInfo && strtoupper((string) ($ipInfo['country_code'] ?? '')) === $countryCode) {
            return true;
        }

        return false;
    }

    /**
     * @return array{country_code: ?string, proxy: bool, hosting: bool}|null
     */
    public function lookupIp(?string $ip): ?array
    {
        if ($ip === null || $ip === '' || $this->isPrivateIp($ip)) {
            return null;
        }

        $cacheMinutes = max(5, (int) config('geo_restrictions.ip_lookup_cache_minutes', 60));
        $cacheKey = 'geo_ip_lookup_v2:' . $ip;

        return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($ip) {
            $fromIpWho = $this->lookupViaIpWho($ip);
            if ($fromIpWho) {
                return $fromIpWho;
            }

            return $this->lookupViaIpApi($ip);
        });
    }

    /**
     * @return array{country_code: ?string, proxy: bool, hosting: bool}|null
     */
    private function lookupViaIpWho(string $ip): ?array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->acceptJson()
                ->get('https://ipwho.is/' . urlencode($ip));

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();
            if (! ($data['success'] ?? false)) {
                return null;
            }

            $code = isset($data['country_code']) ? strtoupper((string) $data['country_code']) : null;

            return [
                'country_code' => $code,
                'proxy' => false,
                'hosting' => false,
            ];
        } catch (\Throwable $e) {
            Log::warning('ipwho.is lookup failed', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array{country_code: ?string, proxy: bool, hosting: bool}|null
     */
    private function lookupViaIpApi(string $ip): ?array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(4)
                ->get('http://ip-api.com/json/' . urlencode($ip), [
                    'fields' => 'status,countryCode,proxy,hosting,query',
                ]);

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();
            if (($data['status'] ?? '') !== 'success') {
                return null;
            }

            return [
                'country_code' => isset($data['countryCode']) ? strtoupper((string) $data['countryCode']) : null,
                'proxy' => (bool) ($data['proxy'] ?? false),
                'hosting' => (bool) ($data['hosting'] ?? false),
            ];
        } catch (\Throwable $e) {
            Log::warning('ip-api lookup failed', ['message' => $e->getMessage()]);

            return null;
        }
    }

    private function phoneCountryCode(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '92') && strlen($digits) >= 12) {
            return 'PK';
        }

        if (preg_match('/^03\d{9}$/', $digits)) {
            return 'PK';
        }

        return null;
    }

    public function deviceIdFromRequest(Request $request): ?string
    {
        return app(RegistrationSecurityService::class)->resolveDeviceId($request);
    }

    public function fingerprintHashFromRequest(Request $request): ?string
    {
        $raw = trim((string) $request->input('client_fp', ''));
        if ($raw === '' || strlen($raw) > 512) {
            return null;
        }

        return hash('sha256', $raw);
    }

    /**
     * Hardware profile — stable across Chrome, Safari, Firefox on the same machine.
     */
    public function hwProfileHashFromRequest(Request $request): ?string
    {
        $fromRequest = trim((string) $request->input('client_hw_fp', ''));
        $fromCookie = trim((string) $request->cookie('htb_hw', ''));

        $raw = $fromRequest !== '' ? $fromRequest : $fromCookie;
        if ($raw === '' || strlen($raw) > 256) {
            return null;
        }

        return hash('sha256', $raw);
    }

    public function persistentIdFromRequest(Request $request): ?string
    {
        $fromRequest = trim((string) $request->input('client_pid', ''));
        $fromCookie = trim((string) $request->cookie('htb_pid', ''));

        $raw = $fromRequest !== '' ? $fromRequest : $fromCookie;
        if ($raw === '' || strlen($raw) > 64) {
            return null;
        }

        return hash('sha256', $raw);
    }

    public function geoCoordsHashFromRequest(Request $request): ?string
    {
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ((! is_numeric($lat) || ! is_numeric($lng)) && $request->hasSession()) {
            $pending = $request->session()->get('registration_otp');
            if (is_array($pending)) {
                if (! is_numeric($lat) && isset($pending['latitude']) && is_numeric($pending['latitude'])) {
                    $lat = $pending['latitude'];
                }
                if (! is_numeric($lng) && isset($pending['longitude']) && is_numeric($pending['longitude'])) {
                    $lng = $pending['longitude'];
                }
                if ((! is_numeric($lat) || ! is_numeric($lng)) && is_array($pending['meta'] ?? null)) {
                    $lat = $lat ?? ($pending['meta']['latitude'] ?? null);
                    $lng = $lng ?? ($pending['meta']['longitude'] ?? null);
                }
            }

            if (! is_numeric($lat)) {
                $lat = $request->session()->get('geo_lat');
            }
            if (! is_numeric($lng)) {
                $lng = $request->session()->get('geo_lng');
            }
        }

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        $lat = round((float) $lat, 4);
        $lng = round((float) $lng, 4);

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || ($lat == 0.0 && $lng == 0.0)) {
            return null;
        }

        return hash('sha256', $lat . ',' . $lng);
    }

    private function phoneFromRequest(Request $request): ?string
    {
        $phone = $request->input('phone');
        if (! is_string($phone) || trim($phone) === '') {
            if ($request->hasSession()) {
                $pending = $request->session()->get('registration_otp');
                if (is_array($pending) && ! empty($pending['phone'])) {
                    $phone = (string) $pending['phone'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return $digits !== '' ? $digits : null;
    }

    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
