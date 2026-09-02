<?php

/**
 * Google Search Console API — OAuth 2.0 (refresh token).
 *
 * 1. Add redirect URI in Google Cloud OAuth client (must match redirect_uri below).
 * 2. Visit /admin/analytics/gsc-auth while logged in as admin.
 * 3. Sign in with a Google account that has access to the Search Console property.
 * 4. Copy GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN into .env
 */

$siteUrl = env('GOOGLE_SEARCH_CONSOLE_SITE_URL', 'https://hottubbuyer.co.uk/');
if (is_string($siteUrl)) {
    $siteUrl = trim($siteUrl);
}

$oauthJson = base_path('gsc_client_secret.json');
if (is_file($oauthJson)) {
    $resolved = realpath($oauthJson);
    if ($resolved !== false) {
        $oauthJson = $resolved;
    }
}

$redirectUri = 'http://127.0.0.1:8000/admin/analytics/gsc-auth';

return [
    'site_url' => $siteUrl !== '' ? $siteUrl : null,
    'oauth_client_json' => ($oauthJson && is_file($oauthJson)) ? $oauthJson : null,
    'redirect_uri' => $redirectUri,
    'refresh_token' => env('GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN'),
];
