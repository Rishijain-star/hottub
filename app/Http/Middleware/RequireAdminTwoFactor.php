<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $next($request);
        }

        $hasPhone = trim((string) $user->phone) !== '';
        $hasAdmin2faOverride = trim((string) config('services.firetext.admin_2fa_to', '')) !== '';
        if (!$hasPhone && !$hasAdmin2faOverride) {
            return redirect()->route('admin.two-factor.show');
        }

        if ((int) session('admin_2fa_ok_user_id') === (int) $user->id) {
            return $next($request);
        }

        if (!$request->routeIs('admin.two-factor.*')) {
            session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('admin.two-factor.show');
    }
}
