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

            return ['lat' => (float)$lat, 'lng' => (float)$lng, 'timezone' => $timezone];
        } catch (\Exception $e) {
            Log::error('Geocoding service exception', ['message' => $e->getMessage()]);
            return null;
        }
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

