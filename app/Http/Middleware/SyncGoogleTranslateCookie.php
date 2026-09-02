<?php

namespace App\Http\Middleware;

use App\Services\LocalizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SyncGoogleTranslateCookie
{
    public function __construct(
        protected LocalizationService $localization,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('localization.google_translate.enabled', true)) {
            return $response;
        }

        $lang = $this->localization->googleTranslateTarget();

        if ($lang) {
            $response->headers->setCookie(new Cookie(
                'googtrans',
                '/en/'.$lang,
                time() + 60 * 60 * 24 * 365,
                '/',
                null,
                false,
                false,
            ));
        } else {
            $response->headers->clearCookie('googtrans', '/');
        }

        return $response;
    }
}
