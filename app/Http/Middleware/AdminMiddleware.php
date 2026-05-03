<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admins only.');
        }

        $user = auth()->user();
        if (in_array($user->status ?? '', ['paused', 'frozen', 'inactive'], true)) {
            abort(403, 'Your administrator account is not active.');
        }

        return $next($request);
    }
}