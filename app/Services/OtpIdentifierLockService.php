<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpIdentifierLockService
{
    /**
     * @var list<string>
     */
    private const IDENTIFIER_TYPES = [
        'hw_profiles',
        'fingerprints',
        'persistent_ids',
        'devices',
        'geo_coords',
    ];

    public function __construct(
        protected GeoRestrictionService $geo,
        protected OtpIdentityLinker $linker,
    ) {}

    public function lockMessage(): string
    {
        $hours = max(1, (int) config('otp_limits.hardware_lock_hours', 24));

        return (string) config(
            'otp_limits.hardware_lock_message',
            'You are locked for ' . $hours . ' hours. Please try again later.'
        );
    }

    public function isLockMessage(?string $message): bool
    {
        if ($message === null || $message === '') {
            return false;
        }

        return str_contains(strtolower($message), 'locked');
    }

    public function isLocked(Request $request): bool
    {
        return $this->lockedUntil($request) !== null;
    }

    public function lockedUntil(Request $request): ?Carbon
    {
        if ($this->geo->skipsChecksOnLocal()) {
            return null;
        }

        $latest = null;

        foreach ($this->flattenIdentifiers($this->identifiersForRequest($request)) as [$type, $id]) {
            $raw = Cache::get($this->lockKey($type, $id));
            if (! is_numeric($raw)) {
                continue;
            }

            $expires = Carbon::createFromTimestamp((int) $raw);
            if ($expires->isFuture() && ($latest === null || $expires->gt($latest))) {
                $latest = $expires;
            }
        }

        return $latest;
    }

    public function assertCanRequestOtp(Request $request, ?string $phone = null): ?string
    {
        if ($this->geo->skipsChecksOnLocal()) {
            return null;
        }

        if ($this->isLocked($request)) {
            return $this->lockMessage();
        }

        return null;
    }

    /**
     * Call immediately before sending an OTP (email/SMS). Blocks the 6th delivery in the window.
     */
    public function assertCanDeliverOtp(Request $request, ?string $phone = null): ?string
    {
        if ($msg = $this->assertCanRequestOtp($request, $phone)) {
            return $msg;
        }

        if ($this->wouldExceedLimit($request, $phone)) {
            $this->applyLock($this->expandedBundle($request, $phone), 'otp_identifier_limit');

            return $this->lockMessage();
        }

        return null;
    }

    /**
     * Record one successful OTP delivery (email or SMS sent).
     */
    public function recordOtpDelivery(Request $request, ?string $phone = null): void
    {
        $this->recordOtpRequest($request, $phone);
    }

    private function recordOtpRequest(Request $request, ?string $phone = null): void
    {
        if ($this->geo->skipsChecksOnLocal()) {
            return;
        }

        $bundle = $this->expandedBundle($request, $phone);
        $windowHours = $this->windowHours();
        $now = now()->timestamp;

        foreach ($this->flattenIdentifiers($bundle) as [$type, $id]) {
            $attempts = $this->pruneAttempts($this->getAttempts($type, $id), $windowHours);
            $attempts[] = $now;
            $this->saveAttempts($type, $id, $attempts, $windowHours);
        }
    }

    public function wouldExceedLimit(Request $request, ?string $phone = null): bool
    {
        $maxAttempts = $this->maxAttempts();
        $windowHours = $this->windowHours();

        foreach ($this->flattenIdentifiers($this->expandedBundle($request, $phone)) as [$type, $id]) {
            $count = count($this->pruneAttempts($this->getAttempts($type, $id), $windowHours));
            if ($count >= $maxAttempts) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>, geo_coords: list<string>}  $bundle
     */
    private function applyLock(array $bundle, string $reason): void
    {
        $lockHours = max(1, (int) config('otp_limits.hardware_lock_hours', 24));
        $until = now()->addHours($lockHours)->timestamp;
        $ttl = now()->addHours($lockHours);

        foreach ($this->flattenIdentifiers($bundle) as [$type, $id]) {
            Cache::put($this->lockKey($type, $id), $until, $ttl);
        }

        Log::warning('OTP identifier lock applied', [
            'reason' => $reason,
            'lock_hours' => $lockHours,
            'identifiers' => array_map(
                fn (array $pair): string => $pair[0] . ':' . substr($pair[1], 0, 12),
                $this->flattenIdentifiers($bundle)
            ),
        ]);
    }

    /**
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>, geo_coords: list<string>}
     */
    private function expandedBundle(Request $request, ?string $phone = null): array
    {
        $bundle = $this->identifiersForRequest($request, $phone);

        if ($phone) {
            $digits = preg_replace('/\D/', '', $phone) ?? '';
            if ($digits !== '') {
                $bundle['phones'][] = $digits;
                $bundle['phones'] = array_values(array_unique($bundle['phones']));
            }
        }

        $expanded = $this->linker->expandBundle($bundle);
        $expanded['geo_coords'] = array_values(array_unique(array_merge(
            $bundle['geo_coords'],
            $expanded['geo_coords'] ?? []
        )));

        return $expanded;
    }

    /**
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>, geo_coords: list<string>}
     */
    private function identifiersForRequest(Request $request, ?string $phone = null): array
    {
        $bundle = $this->linker->collectFromRequest($request, $phone);
        $bundle['geo_coords'] = array_values(array_filter([
            $this->geo->geoCoordsHashFromRequest($request),
        ]));

        return $this->normalizeBundle($bundle);
    }

    /**
     * @param  array<string, mixed>  $bundle
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>, geo_coords: list<string>}
     */
    private function normalizeBundle(array $bundle): array
    {
        $normalized = [
            'devices' => array_values(array_unique(array_filter($bundle['devices'] ?? []))),
            'fingerprints' => array_values(array_unique(array_filter($bundle['fingerprints'] ?? []))),
            'ips' => array_values(array_unique(array_filter($bundle['ips'] ?? []))),
            'phones' => array_values(array_unique(array_filter($bundle['phones'] ?? []))),
            'hw_profiles' => array_values(array_unique(array_filter($bundle['hw_profiles'] ?? []))),
            'persistent_ids' => array_values(array_unique(array_filter($bundle['persistent_ids'] ?? []))),
            'geo_coords' => array_values(array_unique(array_filter($bundle['geo_coords'] ?? []))),
        ];

        return $normalized;
    }

    /**
     * @param  array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>, geo_coords: list<string>}  $bundle
     * @return list<array{0: string, 1: string}>
     */
    private function flattenIdentifiers(array $bundle): array
    {
        $flat = [];

        foreach (self::IDENTIFIER_TYPES as $type) {
            foreach ($bundle[$type] ?? [] as $id) {
                if ($id !== '') {
                    $flat[] = [$type, $id];
                }
            }
        }

        return $flat;
    }

    /**
     * @return list<int>
     */
    private function getAttempts(string $type, string $id): array
    {
        $raw = Cache::get($this->attemptsKey($type, $id));

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $raw)));
    }

    /**
     * @param  list<int>  $attempts
     * @return list<int>
     */
    private function pruneAttempts(array $attempts, int $windowHours): array
    {
        $cutoff = now()->subHours($windowHours)->timestamp;

        return array_values(array_filter($attempts, fn (int $ts): bool => $ts >= $cutoff));
    }

    /**
     * @param  list<int>  $attempts
     */
    private function saveAttempts(string $type, string $id, array $attempts, int $windowHours): void
    {
        Cache::put(
            $this->attemptsKey($type, $id),
            $attempts,
            now()->addHours($windowHours + 1)
        );
    }

    private function attemptsKey(string $type, string $id): string
    {
        return 'otp_id_attempts:' . $type . ':' . $id;
    }

    private function lockKey(string $type, string $id): string
    {
        return 'otp_id_lock:' . $type . ':' . $id;
    }

    private function maxAttempts(): int
    {
        return max(1, (int) config('otp_limits.hardware_max_attempts', 5));
    }

    private function windowHours(): int
    {
        return max(1, (int) config('otp_limits.hardware_window_hours', 5));
    }
}
