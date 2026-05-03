<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSessionMatchesPassword
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $sessionKey = 'auth_password_hash';
        $sessionPasswordHash = (string) $request->session()->get($sessionKey, '');

        if ($sessionPasswordHash === '') {
            $request->session()->put($sessionKey, (string) $user->password);
            return $next($request);
        }

        if (!hash_equals($sessionPasswordHash, (string) $user->password)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your password was changed. Please log in again.',
                ], 401);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your password was changed by admin. Please log in with your new password.']);
        }

        return $next($request);
    }
}
