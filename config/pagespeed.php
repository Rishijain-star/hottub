<?php

/**
 * Google PageSpeed Insights API (Core Web Vitals / Lighthouse lab data).
 */

return [
    'url' => env('PAGESPEED_URL', 'https://www.hottubbuyer.co.uk'),
    'api_endpoint' => env(
        'PAGESPEED_API_ENDPOINT',
        'https://www.googleapis.com/pagespeedonline/v5/runPagespeed'
    ),
    'api_key' => env('PAGESPEED_API_KEY'),
    'cache_ttl' => (int) env('PAGESPEED_CACHE_TTL', 3600),
];
