<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OtpIdentityLinker
{
    private const TTL_DAYS = 30;

    /**
     * Link every identifier from this request so one abuse block catches all related browsers/IPs.
     *
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>}
     */
    public function collectFromRequest(Request $request, ?string $phone = null): array
    {
        $geo = app(GeoRestrictionService::class);

        $bundle = [
            'devices' => array_values(array_filter([$geo->deviceIdFromRequest($request)])),
            'fingerprints' => array_values(array_filter([$geo->fingerprintHashFromRequest($request)])),
            'ips' => array_values(array_filter([$geo->clientIp($request)])),
            'phones' => [],
            'hw_profiles' => array_values(array_filter([$geo->hwProfileHashFromRequest($request)])),
            'persistent_ids' => array_values(array_filter([$geo->persistentIdFromRequest($request)])),
            'geo_coords' => array_values(array_filter([$geo->geoCoordsHashFromRequest($request)])),
        ];

        if ($phone) {
            $digits = preg_replace('/\D/', '', $phone) ?? '';
            if ($digits !== '') {
                $bundle['phones'][] = $digits;
            }
        }

        if ($request->hasSession()) {
            $pendingPhone = $request->session()->get('registration_otp');
            if (is_array($pendingPhone) && ! empty($pendingPhone['phone'])) {
                $digits = preg_replace('/\D/', '', (string) $pendingPhone['phone']) ?? '';
                if ($digits !== '') {
                    $bundle['phones'][] = $digits;
                }
            }
        }

        $bundle['phones'] = array_values(array_unique($bundle['phones']));

        return $bundle;
    }

    /**
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $bundle
     */
    public function record(Request $request, array $bundle): void
    {
        $clusterId = $this->clusterIdForBundle($bundle);
        if ($clusterId === null) {
            return;
        }

        $merged = $this->mergeBundle($this->loadCluster($clusterId), $bundle);
        $this->saveCluster($clusterId, $merged);

        foreach ($merged['devices'] as $id) {
            $this->linkRef('device', $id, $clusterId);
        }
        foreach ($merged['fingerprints'] as $id) {
            $this->linkRef('fingerprint', $id, $clusterId);
        }
        foreach ($merged['ips'] as $id) {
            $this->linkRef('ip', $id, $clusterId);
        }
        foreach ($merged['phones'] as $id) {
            $this->linkRef('phone', $id, $clusterId);
        }
        foreach ($merged['hw_profiles'] as $id) {
            $this->linkRef('hw_profile', $id, $clusterId);
        }
        foreach ($merged['persistent_ids'] as $id) {
            $this->linkRef('persistent_id', $id, $clusterId);
        }
        foreach ($merged['geo_coords'] as $id) {
            $this->linkRef('geo_coords', $id, $clusterId);
        }
    }

    /**
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $bundle
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>}
     */
    public function expandBundle(array $bundle): array
    {
        $merged = $bundle;
        $seenClusters = [];

        foreach (['devices', 'fingerprints', 'ips', 'phones', 'hw_profiles', 'persistent_ids', 'geo_coords'] as $type) {
            $refType = match ($type) {
                'devices' => 'device',
                'fingerprints' => 'fingerprint',
                'ips' => 'ip',
                'phones' => 'phone',
                'hw_profiles' => 'hw_profile',
                'persistent_ids' => 'persistent_id',
                'geo_coords' => 'geo_coords',
            };

            foreach ($bundle[$type] as $identifier) {
                $clusterId = Cache::get($this->refKey($refType, $identifier));
                if (! is_string($clusterId) || $clusterId === '' || isset($seenClusters[$clusterId])) {
                    continue;
                }
                $seenClusters[$clusterId] = true;
                $merged = $this->mergeBundle($merged, $this->loadCluster($clusterId));
            }
        }

        return $merged;
    }

    /**
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $bundle
     */
    private function clusterIdForBundle(array $bundle): ?string
    {
        $parts = [];
        foreach (['devices', 'fingerprints', 'ips', 'phones', 'hw_profiles', 'persistent_ids', 'geo_coords'] as $key) {
            foreach ($bundle[$key] as $value) {
                $parts[] = $key . ':' . $value;
            }
        }

        if ($parts === []) {
            return null;
        }

        sort($parts);

        return hash('sha256', implode('|', $parts));
    }

    /**
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>}
     */
    private function loadCluster(string $clusterId): array
    {
        $data = Cache::get($this->clusterKey($clusterId));

        return is_array($data) ? $this->normalizeBundle($data) : $this->emptyBundle();
    }

    /**
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $bundle
     */
    private function saveCluster(string $clusterId, array $bundle): void
    {
        Cache::put($this->clusterKey($clusterId), $bundle, now()->addDays(self::TTL_DAYS));
    }

    private function linkRef(string $type, string $identifier, string $clusterId): void
    {
        Cache::put($this->refKey($type, $identifier), $clusterId, now()->addDays(self::TTL_DAYS));
    }

    private function clusterKey(string $clusterId): string
    {
        return 'otp_abuse_cluster:' . $clusterId;
    }

    private function refKey(string $type, string $identifier): string
    {
        return 'otp_abuse_ref:' . $type . ':' . $identifier;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>}
     */
    private function normalizeBundle(array $data): array
    {
        return [
            'devices' => array_values(array_unique(array_filter($data['devices'] ?? []))),
            'fingerprints' => array_values(array_unique(array_filter($data['fingerprints'] ?? []))),
            'ips' => array_values(array_unique(array_filter($data['ips'] ?? []))),
            'phones' => array_values(array_unique(array_filter($data['phones'] ?? []))),
            'hw_profiles' => array_values(array_unique(array_filter($data['hw_profiles'] ?? []))),
            'persistent_ids' => array_values(array_unique(array_filter($data['persistent_ids'] ?? []))),
            'geo_coords' => array_values(array_unique(array_filter($data['geo_coords'] ?? []))),
        ];
    }

    /**
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>}
     */
    private function emptyBundle(): array
    {
        return [
            'devices' => [],
            'fingerprints' => [],
            'ips' => [],
            'phones' => [],
            'hw_profiles' => [],
            'persistent_ids' => [],
            'geo_coords' => [],
        ];
    }

    /**
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $a
     * @param array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>} $b
     * @return array{devices: list<string>, fingerprints: list<string>, ips: list<string>, phones: list<string>, hw_profiles: list<string>, persistent_ids: list<string>}
     */
    private function mergeBundle(array $a, array $b): array
    {
        return $this->normalizeBundle([
            'devices' => array_merge($a['devices'], $b['devices']),
            'fingerprints' => array_merge($a['fingerprints'], $b['fingerprints']),
            'ips' => array_merge($a['ips'], $b['ips']),
            'phones' => array_merge($a['phones'], $b['phones']),
            'hw_profiles' => array_merge($a['hw_profiles'], $b['hw_profiles']),
            'persistent_ids' => array_merge($a['persistent_ids'], $b['persistent_ids']),
            'geo_coords' => array_merge($a['geo_coords'], $b['geo_coords']),
        ]);
    }
}
