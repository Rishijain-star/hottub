<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FullAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $u = auth()->user();
        if (!$u || !$u->isFullAdmin()) {
            abort(403, 'Only the primary administrator can access this section.');
        }

        return $next($request);
    }
}
