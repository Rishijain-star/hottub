<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function geocode(string $postcode): ?array
    {
        $key = config('services.opencage.key');
        if (!$key) {
            Log::error('OpenCage API key is not configured.');
            return null;
        }

        try {
            $resp = Http::timeout(10)->get('https://api.opencagedata.com/geocode/v1/json', [
                'q' => $postcode,
                'key' => $key,
            ]);

            if (!$resp->ok()) {
                Log::error('Geocoding API request failed', [
                    'status' => $resp->status(),
                    'response' => $resp->body(),
                ]);
                return null;
            }

            $data = $resp->json();
            Log::info('Geocoding API Response', ['data' => $data]);

            if (empty($data['results'])) {
                Log::warning('Geocoding API returned no results for postcode', ['postcode' => $postcode]);
                return null;
            }

            $res = $data['results'][0]['geometry'] ?? null;
            if (!$res) {
                Log::warning('Geocoding API response missing geometry', ['results' => $data['results'][0]]);
                return null;
            }

            $lat = $res['lat'] ?? null;
            $lng = $res['lng'] ?? null;

            $timezone = $data['results'][0]['annotations']['timezone']['name'] ?? null;

            if ($lat === null || $lng === null) {
                Log::warning('Geocoding API response missing lat/lng', ['geometry' => $res]);
                return null;
            }

            $countryCode = $data['results'][0]['components']['country_code'] ?? null;

            return [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'timezone' => $timezone,
                'country_code' => $countryCode ? strtoupper((string) $countryCode) : null,
            ];
        } catch (\Exception $e) {
            Log::error('Geocoding service exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Reverse geocode coordinates to country (and timezone when available).
     *
     * @return array{lat: float, lng: float, country_code: ?string, timezone: ?string}|null
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $key = config('services.opencage.key');
        if ($key) {
            try {
                $resp = Http::timeout(10)->get('https://api.opencagedata.com/geocode/v1/json', [
                    'q' => $lat.','.$lng,
                    'key' => $key,
                ]);

                if ($resp->ok() && ! empty($resp->json('results'))) {
                    $result = $resp->json('results.0');
                    $countryCode = $result['components']['country_code'] ?? null;

                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'country_code' => $countryCode ? strtoupper((string) $countryCode) : null,
                        'timezone' => $result['annotations']['timezone']['name'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('OpenCage reverse geocode failed', ['message' => $e->getMessage()]);
            }
        }

        try {
            $resp = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'HotTubBuyer/1.0 (localization)'])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                    'zoom' => 3,
                ]);

            if ($resp->ok()) {
                $code = $resp->json('address.country_code');

                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'country_code' => $code ? strtoupper((string) $code) : null,
                    'timezone' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Nominatim reverse geocode failed', ['message' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Calculate distance between two points in miles using Haversine formula
     */
    public function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3958.8; // Miles

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

