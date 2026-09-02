<?php

/**
 * Europe + USA locales and currencies (no Indian language locales in selector).
 *
 * Admin credit plan prices are stored in GBP.
 * Catalog product/service prices remain in GBP.
 * Display amounts use daily GBP-based rates (currency_rates table).
 */

return [
    'base_currency' => 'GBP',

    'api_url' => env('CURRENCY_API_URL', 'https://open.er-api.com/v6/latest/GBP'),

    'storage' => [
        'products' => 'GBP',
        'credit_plans' => 'GBP',
    ],

    'default_country' => 'GB',

    'currencies' => [
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'decimals' => 2],
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'decimals' => 2],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'decimals' => 2],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'decimals' => 2],
        'CHF' => ['symbol' => 'CHF ', 'name' => 'Swiss Franc', 'decimals' => 2],
        'SEK' => ['symbol' => 'kr', 'name' => 'Swedish Krona', 'decimals' => 2],
        'NOK' => ['symbol' => 'kr', 'name' => 'Norwegian Krone', 'decimals' => 2],
        'DKK' => ['symbol' => 'kr', 'name' => 'Danish Krone', 'decimals' => 2],
        'PLN' => ['symbol' => 'zł', 'name' => 'Polish Zloty', 'decimals' => 2],
        'CZK' => ['symbol' => 'Kč', 'name' => 'Czech Koruna', 'decimals' => 2],
    ],

    /**
     * ISO country code → locale + currency (set at registration from postcode).
     */
    'countries' => [
        'GB' => ['locale' => 'en_GB', 'currency' => 'GBP'],
        'US' => ['locale' => 'en_US', 'currency' => 'USD'],
        'IN' => ['locale' => 'en_GB', 'currency' => 'INR'],
        'DE' => ['locale' => 'de_DE', 'currency' => 'EUR'],
        'FR' => ['locale' => 'fr_FR', 'currency' => 'EUR'],
        'ES' => ['locale' => 'es_ES', 'currency' => 'EUR'],
        'IT' => ['locale' => 'it_IT', 'currency' => 'EUR'],
        'NL' => ['locale' => 'nl_NL', 'currency' => 'EUR'],
        'BE' => ['locale' => 'fr_FR', 'currency' => 'EUR'],
        'AT' => ['locale' => 'de_DE', 'currency' => 'EUR'],
        'PL' => ['locale' => 'pl_PL', 'currency' => 'PLN'],
        'SE' => ['locale' => 'sv_SE', 'currency' => 'SEK'],
        'NO' => ['locale' => 'nb_NO', 'currency' => 'NOK'],
        'DK' => ['locale' => 'da_DK', 'currency' => 'DKK'],
        'CH' => ['locale' => 'de_DE', 'currency' => 'CHF'],
        'CZ' => ['locale' => 'cs_CZ', 'currency' => 'CZK'],
        'IE' => ['locale' => 'en_GB', 'currency' => 'EUR'],
        'PT' => ['locale' => 'pt_PT', 'currency' => 'EUR'],
        'FI' => ['locale' => 'fi_FI', 'currency' => 'EUR'],
        'GR' => ['locale' => 'el_GR', 'currency' => 'EUR'],
        'LU' => ['locale' => 'fr_FR', 'currency' => 'EUR'],
    ],

    'locales' => [
        'en_GB' => [
            'label' => 'English',
            'flag' => '🇬🇧',
            'currency' => 'GBP',
            'hreflang' => 'en-GB',
            'country' => 'United Kingdom',
            'google_lang' => null,
        ],
        'en_US' => [
            'label' => 'English',
            'flag' => '🇺🇸',
            'currency' => 'USD',
            'hreflang' => 'en-US',
            'country' => 'United States',
            'google_lang' => null,
        ],
        'de_DE' => [
            'label' => 'Deutsch',
            'flag' => '🇩🇪',
            'currency' => 'EUR',
            'hreflang' => 'de-DE',
            'country' => 'Germany',
            'google_lang' => 'de',
        ],
        'fr_FR' => [
            'label' => 'Français',
            'flag' => '🇫🇷',
            'currency' => 'EUR',
            'hreflang' => 'fr-FR',
            'country' => 'France',
            'google_lang' => 'fr',
        ],
        'es_ES' => [
            'label' => 'Español',
            'flag' => '🇪🇸',
            'currency' => 'EUR',
            'hreflang' => 'es-ES',
            'country' => 'Spain',
            'google_lang' => 'es',
        ],
        'it_IT' => [
            'label' => 'Italiano',
            'flag' => '🇮🇹',
            'currency' => 'EUR',
            'hreflang' => 'it-IT',
            'country' => 'Italy',
            'google_lang' => 'it',
        ],
        'nl_NL' => [
            'label' => 'Nederlands',
            'flag' => '🇳🇱',
            'currency' => 'EUR',
            'hreflang' => 'nl-NL',
            'country' => 'Netherlands',
            'google_lang' => 'nl',
        ],
        'pl_PL' => [
            'label' => 'Polski',
            'flag' => '🇵🇱',
            'currency' => 'PLN',
            'hreflang' => 'pl-PL',
            'country' => 'Poland',
            'google_lang' => 'pl',
        ],
        'sv_SE' => [
            'label' => 'Svenska',
            'flag' => '🇸🇪',
            'currency' => 'SEK',
            'hreflang' => 'sv-SE',
            'country' => 'Sweden',
            'google_lang' => 'sv',
        ],
        'pt_PT' => [
            'label' => 'Português',
            'flag' => '🇵🇹',
            'currency' => 'EUR',
            'hreflang' => 'pt-PT',
            'country' => 'Portugal',
            'google_lang' => 'pt',
        ],
        'da_DK' => [
            'label' => 'Dansk',
            'flag' => '🇩🇰',
            'currency' => 'DKK',
            'hreflang' => 'da-DK',
            'country' => 'Denmark',
            'google_lang' => 'da',
        ],
        'nb_NO' => [
            'label' => 'Norsk',
            'flag' => '🇳🇴',
            'currency' => 'NOK',
            'hreflang' => 'nb-NO',
            'country' => 'Norway',
            'google_lang' => 'no',
        ],
        'cs_CZ' => [
            'label' => 'Čeština',
            'flag' => '🇨🇿',
            'currency' => 'CZK',
            'hreflang' => 'cs-CZ',
            'country' => 'Czechia',
            'google_lang' => 'cs',
        ],
        'fi_FI' => [
            'label' => 'Suomi',
            'flag' => '🇫🇮',
            'currency' => 'EUR',
            'hreflang' => 'fi-FI',
            'country' => 'Finland',
            'google_lang' => 'fi',
        ],
        'el_GR' => [
            'label' => 'Ελληνικά',
            'flag' => '🇬🇷',
            'currency' => 'EUR',
            'hreflang' => 'el-GR',
            'country' => 'Greece',
            'google_lang' => 'el',
        ],
    ],

    /** Locales hidden from manual selector (still used for geo fallback). */
    'excluded_selector_locales' => ['en_IN'],

    'default_locale' => env('APP_LOCALE', 'en_GB'),
    'fallback_locale' => 'en_GB',
    'default_currency' => 'GBP',

    /**
     * Auto-translate full page via Google Translate for non-English locales.
     * Covers DB content and any strings missing from lang files.
     */
    'google_translate' => [
        'enabled' => env('GOOGLE_TRANSLATE_ENABLED', true),
    ],
];
