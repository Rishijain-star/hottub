<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next)
    {
        $u = auth()->user();
        if (!$u || !$u->isUser()) {
            return $next($request);
        }

        if ($u->phone_verified_at || !$u->phone) {
            return $next($request);
        }

        if ($request->routeIs('verify.phone', 'verify.phone.submit', 'verify.phone.resend', 'logout')) {
            return $next($request);
        }

        return redirect()->route('verify.phone');
    }
}
