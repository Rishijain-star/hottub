<?php

return [
    'enabled' => (bool) env('GEO_RESTRICTIONS_ENABLED', false),

    // Comma-separated ISO country codes (e.g. PK). Empty = no country blocks.
    'blocked_countries' => array_values(array_filter(array_map(
        'strtoupper',
        array_map('trim', explode(',', (string) env('GEO_BLOCKED_COUNTRIES', '')))
    ))),

    // Skip checks on local dev unless GEO_RESTRICTIONS_TEST=true
    'skip_local' => (bool) env('GEO_RESTRICTIONS_SKIP_LOCAL', true),

    'test_block' => (bool) env('GEO_RESTRICTIONS_TEST', false),

    // Optional: block login/register from VPN/datacenter IPs (affects all countries). Default off.
    'block_proxy_on_auth' => (bool) env('GEO_BLOCK_PROXY_ON_AUTH', false),

    'ip_lookup_cache_minutes' => (int) env('GEO_IP_LOOKUP_CACHE_MINUTES', 60),

    'block_cookie_name' => env('GEO_BLOCK_COOKIE_NAME', 'htb_region_blocked'),
    'block_cookie_minutes' => (int) env('GEO_BLOCK_COOKIE_MINUTES', 525600),
];
