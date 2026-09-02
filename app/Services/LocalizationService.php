<?php

namespace App\Services;

use App\Models\User;

class LocalizationService
{
    public function __construct(
        protected CurrencyService $currency,
        protected LocationRegionService $regions,
    ) {}

    public function currentLocale(): string
    {
        $locale = session('locale', config('localization.default_locale', 'en_GB'));
        if ($locale === 'en') {
            $locale = 'en_GB';
        }
        $supported = array_keys(config('localization.locales', []));

        return in_array($locale, $supported, true) ? $locale : 'en_GB';
    }

    public function setLocale(string $locale): void
    {
        $locales = config('localization.locales', []);
        if (! isset($locales[$locale])) {
            return;
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);
        $this->currency->setCurrency($locales[$locale]['currency']);
    }

    /**
     * @return array<string, array{label: string, flag: string, currency: string, hreflang: string}>
     */
    public function availableLocales(): array
    {
        $locales = config('localization.locales', []);
        $exclude = config('localization.excluded_selector_locales', []);

        return array_diff_key($locales, array_flip($exclude));
    }

    /** Google Translate language code for the active locale, or null for English. */
    public function googleTranslateTarget(?string $locale = null): ?string
    {
        if (! config('localization.google_translate.enabled', true)) {
            return null;
        }

        $locale ??= $this->currentLocale();
        $meta = config('localization.locales.'.$locale, []);

        $code = $meta['google_lang'] ?? null;

        return filled($code) ? (string) $code : null;
    }

    /**
     * @param  array{country_code: string, locale: string, currency: string, source?: string}  $region
     */
    public function applyRegion(array $region): void
    {
        session([
            'locale' => $region['locale'],
            'currency' => $region['currency'],
            'geo_country' => $region['country_code'],
        ]);
        app()->setLocale($region['locale']);
        $this->currency->setCurrency($region['currency']);
        $this->currency->ensureRatesExist();
    }

    public function applyFromSession(): void
    {
        $locale = $this->currentLocale();
        app()->setLocale($locale);

        $currency = session('currency');
        if ($currency && isset(config('localization.currencies')[$currency])) {
            $this->currency->setCurrency($currency);
        } else {
            $locales = config('localization.locales', []);
            if (isset($locales[$locale]['currency'])) {
                $this->currency->setCurrency($locales[$locale]['currency']);
            }
        }

        $this->currency->ensureRatesExist();
    }

    /**
     * Priority: live session coordinates → user registration coordinates → postcode → stored prefs → session.
     */
    public function applyForRequest(?User $user): void
    {
        if (session('locale_manual') || session('currency_manual')) {
            $this->applyFromSession();

            return;
        }

        $sessionLat = session('geo_lat');
        $sessionLng = session('geo_lng');
        if ($this->validCoordinates($sessionLat, $sessionLng)) {
            $region = $this->regions->resolve((float) $sessionLat, (float) $sessionLng, null);
            $this->applyRegion($region);

            return;
        }

        if ($user) {
            $lat = $user->registration_lat !== null ? (float) $user->registration_lat : null;
            $lng = $user->registration_lng !== null ? (float) $user->registration_lng : null;
            if ($this->validCoordinates($lat, $lng)) {
                $region = $this->regions->resolve($lat, $lng, $user->postcode);
                $this->applyRegion($region);

                return;
            }

            if (! empty($user->postcode)) {
                $region = $this->regions->resolve(null, null, (string) $user->postcode);
                $this->applyRegion($region);

                return;
            }

            if ($user->preferred_locale) {
                session(['locale' => $user->preferred_locale]);
                app()->setLocale($user->preferred_locale);
                if ($user->preferred_currency) {
                    $this->currency->setCurrency($user->preferred_currency);
                }
                $this->currency->ensureRatesExist();

                return;
            }
        }

        // No GPS yet (or denied): English until location is resolved, unless user picked manually.
        if (session('geo_unavailable') || ! session('geo_resolved_at')) {
            $this->applyDefaultGuestLocale();

            return;
        }

        $this->applyFromSession();
    }

    /** English + default currency for guests without a resolved location. */
    public function applyDefaultGuestLocale(): void
    {
        $locale = config('localization.default_locale', 'en_GB');
        $currency = config('localization.default_currency', 'GBP');

        session(['locale' => $locale, 'currency' => $currency]);
        app()->setLocale($locale);
        $this->currency->setCurrency($currency);
        $this->currency->ensureRatesExist();
    }

    /** User denied GPS or geolocation is unavailable — keep English defaults. */
    public function markGeoUnavailable(): void
    {
        session([
            'geo_unavailable' => true,
            'geo_resolved_at' => now()->toIso8601String(),
        ]);
        session()->forget(['geo_lat', 'geo_lng']);
        $this->applyDefaultGuestLocale();
    }

    /**
     * @return array{country_code: string, locale: string, currency: string, source: string}
     */
    public function resolveAndPersistForUser(
        User $user,
        ?string $postcode = null,
        ?float $lat = null,
        ?float $lng = null,
    ): array {
        $region = $this->regions->resolve($lat, $lng, $postcode);

        $user->country_code = $region['country_code'];
        $user->preferred_locale = $region['locale'];
        $user->preferred_currency = $region['currency'];
        if ($this->validCoordinates($lat, $lng)) {
            $user->registration_lat = $lat;
            $user->registration_lng = $lng;
        }
        $user->save();

        session([
            'geo_lat' => $lat,
            'geo_lng' => $lng,
        ]);
        $this->applyRegion($region);

        return $region;
    }

    /**
     * Store live browser coordinates in session (and user record when logged in).
     *
     * @return array{country_code: string, locale: string, currency: string, source: string}
     */
    public function storeLiveCoordinates(float $lat, float $lng, ?User $user = null): array
    {
        session([
            'geo_lat' => $lat,
            'geo_lng' => $lng,
            'geo_resolved_at' => now()->toIso8601String(),
        ]);

        $postcode = $user?->postcode;
        $region = $this->regions->resolve($lat, $lng, $postcode);
        $this->applyRegion($region);

        if ($user) {
            $user->registration_lat = $lat;
            $user->registration_lng = $lng;
            $user->country_code = $region['country_code'];
            $user->preferred_locale = $region['locale'];
            $user->preferred_currency = $region['currency'];
            $user->save();
        }

        return $region;
    }

    /** @deprecated Use applyForRequest() */
    public function applyForUser(?User $user): void
    {
        $this->applyForRequest($user);
    }

    protected function validCoordinates(mixed $lat, mixed $lng): bool
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return false;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && ! ($lat == 0.0 && $lng == 0.0);
    }
}
