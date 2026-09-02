<?php

/**
 * Google Analytics 4 (Data API) configuration.
 *
 * Requires:
 * - GOOGLE_ANALYTICS_PROPERTY_ID (numeric GA4 property ID — admin Data API)
 * - GOOGLE_APPLICATION_CREDENTIALS (path to service account JSON — admin Data API)
 * - GOOGLE_ANALYTICS_MEASUREMENT_ID (G-XXXXXXXX — browser gtag on public site)
 *
 * Windows .env tip: use forward slashes or a relative path, e.g.
 *   GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/analytics.json
 * Unquoted D:\paths can lose backslashes when Dotenv parses escape sequences.
 *
 * The service account email must be granted at least Viewer access on the GA4 property.
 */

$credentials = env('GOOGLE_APPLICATION_CREDENTIALS');

if (is_string($credentials)) {
    $credentials = trim($credentials);
}

// Relative path from project root (recommended for .env).
if ($credentials && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $credentials) && ! str_starts_with($credentials, '/')) {
    $credentials = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $credentials));
} elseif ($credentials) {
    $credentials = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $credentials);
}

$defaultCredentials = storage_path('app/google/analytics.json');

// Fallback when env is empty or path is wrong (e.g. mangled Windows path in .env).
if ((! $credentials || ! is_file($credentials)) && is_file($defaultCredentials)) {
    $credentials = $defaultCredentials;
}

if ($credentials && is_file($credentials)) {
    $resolved = realpath($credentials);
    if ($resolved !== false) {
        $credentials = $resolved;
    }
}

$measurementId = env('GOOGLE_ANALYTICS_MEASUREMENT_ID');
if (is_string($measurementId)) {
    $measurementId = trim($measurementId);
}
if ($measurementId === '') {
    $measurementId = null;
}

return [
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
    'credentials_path' => $credentials ?: null,
    'measurement_id' => $measurementId,
];
