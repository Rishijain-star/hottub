<?php

namespace App\Http\Middleware;

use App\Services\GeoRestrictionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockRestrictedRegions
{
    public function __construct(
        protected GeoRestrictionService $geo,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if (! $this->geo->isAccessDenied($request)) {
            return $next($request);
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $this->geo->genericDenyMessage(),
            ], 403)->withCookie($this->geo->blockCookie());
        }

        return response()
            ->view('pages.region-restricted', [], 403)
            ->withCookie($this->geo->blockCookie());
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->is(
            'up',
            'webhooks/*',
            'storage/*',
        );
    }
}
