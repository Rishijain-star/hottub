<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class EnsureDeviceFingerprint
{
    public function handle(Request $request, Closure $next)
    {
        $cookieName = (string) config('registration.device_cookie_name', 'htb_did');
        $response = $next($request);

        if ($request->hasCookie($cookieName)) {
            return $response;
        }

        $minutes = max(60, (int) config('registration.device_cookie_minutes', 525600));
        $secure = config('session.secure');
        if ($secure === null) {
            $secure = $request->isSecure();
        }

        return $response->withCookie(new Cookie(
            $cookieName,
            (string) Str::uuid(),
            $minutes,
            '/',
            config('session.domain'),
            (bool) $secure,
            true,
            false,
            Cookie::SAMESITE_LAX
        ));
    }
}
