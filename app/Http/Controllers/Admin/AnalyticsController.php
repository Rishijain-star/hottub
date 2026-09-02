<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use App\Services\GoogleSearchConsoleService;
use App\Services\MicrosoftClarityService;
use App\Services\PageSpeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Google Analytics reporting (GA4 Data API).
 */
class AnalyticsController extends Controller
{
    /**
     * GA4 analytics dashboard (Step 1).
     * GET /admin/analytics?days=7|30|90
     */
    public function index(
        Request $request,
        GoogleAnalyticsService $analytics,
        GoogleSearchConsoleService $searchConsole,
        MicrosoftClarityService $clarity,
        PageSpeedService $pageSpeed,
    ): View {
        $days = (int) $request->query('days', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        $result = $analytics->getDashboardStep1($days);
        $searchConsoleResult = $searchConsole->getReport($days);
        $clarityResult = $clarity->getReport($days);
        $pageSpeedResult = $pageSpeed->getReport();

        $gaOk = $result['ok'] ?? false;
        $contentImprovements = $gaOk ? $analytics->getContentImprovements($days) : ['ok' => false, 'error' => 'Requires GA4 data.'];
        $browserCompatibility = $gaOk ? $analytics->getBrowserCompatibility($days) : ['ok' => false, 'error' => 'Requires GA4 data.'];
        $brokenLinks404 = $gaOk ? $analytics->getBrokenLinks404($days) : ['ok' => false, 'error' => 'Requires GA4 data.'];
        $returningVisitorFrequency = $gaOk ? $analytics->getReturningVisitorFrequency($days) : ['ok' => false, 'error' => 'Requires GA4 data.'];
        $popularFilterCategories = $gaOk ? $analytics->getPopularFilterCategories($days) : ['ok' => false, 'error' => 'Requires GA4 data.'];
        $gscIndexedPages = $searchConsole->getIndexedPagesFromSitemaps();
        $pageSpeedAccessibility = $pageSpeed->getAccessibilityScores();

        return view('admin.analytics.index', [
            'result' => $result,
            'searchConsoleResult' => $searchConsoleResult,
            'clarityResult' => $clarityResult,
            'pageSpeedResult' => $pageSpeedResult,
            'contentImprovements' => $contentImprovements,
            'gscIndexedPages' => $gscIndexedPages,
            'browserCompatibility' => $browserCompatibility,
            'brokenLinks404' => $brokenLinks404,
            'pageSpeedAccessibility' => $pageSpeedAccessibility,
            'returningVisitorFrequency' => $returningVisitorFrequency,
            'popularFilterCategories' => $popularFilterCategories,
            'days' => $days,
        ]);
    }

    /**
     * One-time OAuth: connect Search Console and obtain refresh token for .env
     * GET /admin/analytics/gsc-auth  (callback with ?code=... on same URL)
     */
    public function gscAuth(Request $request, GoogleSearchConsoleService $searchConsole): RedirectResponse|View
    {
        if ($request->filled('code')) {
            try {
                $refreshToken = $searchConsole->exchangeAuthorizationCode((string) $request->query('code'));

                return view('admin.analytics.gsc-auth', [
                    'success' => true,
                    'refreshToken' => $refreshToken,
                    'redirectUri' => config('search_console.redirect_uri'),
                ]);
            } catch (\Throwable $e) {
                return view('admin.analytics.gsc-auth', [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'redirectUri' => config('search_console.redirect_uri'),
                ]);
            }
        }

        if ($request->filled('error')) {
            return view('admin.analytics.gsc-auth', [
                'success' => false,
                'error' => (string) $request->query('error_description', $request->query('error')),
                'redirectUri' => config('search_console.redirect_uri'),
            ]);
        }

        if (! is_file(config('search_console.oauth_client_json') ?? '')) {
            return view('admin.analytics.gsc-auth', [
                'success' => false,
                'error' => 'OAuth client JSON not found. Place client_secret.json in project root or set GOOGLE_SEARCH_CONSOLE_OAUTH_CLIENT_JSON.',
                'redirectUri' => config('search_console.redirect_uri'),
            ]);
        }

        return redirect()->away($searchConsole->createAuthorizationUrl());
    }

    /**
     * Temporary diagnostics: config + file checks (no JSON key contents).
     * GET /admin/analytics/debug-config
     */
    public function debugConfig(GoogleAnalyticsService $analytics): JsonResponse
    {
        $credentialsPath = config('analytics.credentials_path');
        $envCredentials = env('GOOGLE_APPLICATION_CREDENTIALS');

        return response()->json([
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            'analytics' => [
                'property_id' => config('analytics.property_id'),
                'credentials_path' => $credentialsPath,
            ],
            'credentials_file' => [
                'file_exists' => is_string($credentialsPath) && $credentialsPath !== ''
                    ? file_exists($credentialsPath)
                    : false,
                'is_readable' => is_string($credentialsPath) && $credentialsPath !== ''
                    ? is_readable($credentialsPath)
                    : false,
                'realpath' => is_string($credentialsPath) && $credentialsPath !== '' && file_exists($credentialsPath)
                    ? realpath($credentialsPath)
                    : null,
            ],
            'env_reading' => [
                'GOOGLE_ANALYTICS_PROPERTY_ID' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
                'GOOGLE_APPLICATION_CREDENTIALS_length' => is_string($envCredentials) ? strlen($envCredentials) : 0,
                'GOOGLE_APPLICATION_CREDENTIALS_looks_like_windows_path' => is_string($envCredentials)
                    && preg_match('/^[A-Za-z]:/', $envCredentials) === 1,
                'GOOGLE_APPLICATION_CREDENTIALS_contains_separator' => is_string($envCredentials)
                    && (str_contains($envCredentials, '/') || str_contains($envCredentials, '\\')),
            ],
            'checks' => $analytics->configurationDiagnostics(),
            'hints' => [
                'clear_config_cache' => 'php artisan config:clear',
                'recommended_credentials_env' => 'GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/analytics.json',
                'windows_env_tip' => 'Use forward slashes (D:/laragon/...) or quote the path; unquoted backslashes may be stripped by Dotenv.',
            ],
        ]);
    }

    /**
     * Test endpoint: active users, sessions, page views.
     * JSON: /admin/analytics/test?format=json
     * HTML: /admin/analytics/test
     */
    public function test(Request $request, GoogleAnalyticsService $analytics): View|JsonResponse
    {
        $startDate = $request->query('start_date', '7daysAgo');
        $endDate = $request->query('end_date', 'today');

        $result = $analytics->getTestMetrics($startDate, $endDate);

        if ($request->expectsJson() || $request->query('format') === 'json') {
            return response()->json($result, $result['ok'] ? 200 : 503);
        }

        return view('admin.analytics.test', [
            'result' => $result,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
