<?php

namespace App\Services;

/**
 * Resolve country, locale, and currency — coordinates first, postcode fallback.
 */
class LocationRegionService
{
    public function __construct(
        protected GeocodingService $geocoding,
    ) {}

    /**
     * @return array{country_code: string, locale: string, currency: string, source: string}
     */
    public function resolve(?float $lat, ?float $lng, ?string $postcode = null): array
    {
        if ($this->validCoordinates($lat, $lng)) {
            $countryCode = $this->countryFromCoordinates($lat, $lng);
            if ($countryCode) {
                return $this->regionForCountry($countryCode, 'coordinates');
            }
        }

        if ($postcode !== null && trim($postcode) !== '') {
            return $this->resolveFromPostcode(trim($postcode));
        }

        return $this->defaultRegion('default');
    }

    /**
     * @return array{country_code: string, locale: string, currency: string, source: string}
     */
    protected function resolveFromPostcode(string $postcode): array
    {
        $countryCode = $this->detectCountryFromPostcode($postcode);

        if ($countryCode) {
            return $this->regionForCountry($countryCode, 'postcode');
        }

        return $this->defaultRegion('postcode');
    }

    /**
     * @return array{country_code: string, locale: string, currency: string, source: string}
     */
    protected function regionForCountry(string $countryCode, string $source): array
    {
        $countryCode = strtoupper($countryCode);
        $countries = config('localization.countries', []);

        if (isset($countries[$countryCode])) {
            return [
                'country_code' => $countryCode,
                'locale' => $countries[$countryCode]['locale'],
                'currency' => $countries[$countryCode]['currency'],
                'source' => $source,
            ];
        }

        return $this->defaultRegion($source);
    }

    /**
     * @return array{country_code: string, locale: string, currency: string, source: string}
     */
    protected function defaultRegion(string $source): array
    {
        $defaultCountry = config('localization.default_country', 'GB');
        $countries = config('localization.countries', []);
        $fallback = $countries[$defaultCountry] ?? [
            'locale' => config('localization.default_locale', 'en_GB'),
            'currency' => config('localization.default_currency', 'GBP'),
        ];

        return [
            'country_code' => $defaultCountry,
            'locale' => $fallback['locale'] ?? 'en_GB',
            'currency' => $fallback['currency'] ?? 'GBP',
            'source' => $source,
        ];
    }

    protected function countryFromCoordinates(float $lat, float $lng): ?string
    {
        $geo = $this->geocoding->reverseGeocode($lat, $lng);

        return ! empty($geo['country_code']) ? strtoupper($geo['country_code']) : null;
    }

    protected function detectCountryFromPostcode(string $postcode): ?string
    {
        $normalized = strtoupper(preg_replace('/\s+/', ' ', $postcode) ?? $postcode);

        if (preg_match('/^\d{6}$/', $normalized)) {
            return 'IN';
        }

        if (preg_match('/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i', $normalized)) {
            return 'GB';
        }

        if (preg_match('/^\d{3}\s?\d{2}$/', $normalized)) {
            return 'SE';
        }

        if (preg_match('/^\d{5}$/', $normalized)) {
            $geo = $this->geocoding->geocode($postcode);

            return ! empty($geo['country_code']) ? strtoupper($geo['country_code']) : null;
        }

        if (preg_match('/^\d{5}-\d{4}$/', $normalized)) {
            return 'US';
        }

        $geo = $this->geocoding->geocode($postcode);

        return ! empty($geo['country_code']) ? strtoupper($geo['country_code']) : null;
    }

    protected function validCoordinates(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }

        return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && ! ($lat == 0.0 && $lng == 0.0);
    }
}
