<?php

/**
 * Microsoft Clarity — dashboard links + Data Export API.
 *
 * API token: Clarity project → Settings → Data Export → Generate API token.
 * Official endpoint: project-live-insights (last 1–3 days, max 10 requests/day).
 */

return [
    'project_id' => env('CLARITY_PROJECT_ID', 'x087zurmb1'),
    'api_token' => env('CLARITY_API_TOKEN'),
    'api_endpoint' => env(
        'CLARITY_API_ENDPOINT',
        'https://www.clarity.ms/export-data/api/v1/project-live-insights'
    ),
    'dashboard_base' => 'https://clarity.microsoft.com/projects/view',
];
