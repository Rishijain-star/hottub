<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isUser()) {
            abort(403, 'Access denied. Customers only.');
        }

        // Freshly check status from DB to ensure real-time locking
        $user = \App\Models\User::find(auth()->id());
        if ($user && ($user->status === 'paused' || $user->status === 'frozen')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Your account is ' . $user->status . '. Please contact support.',
                    'restricted' => true
                ], 403);
            }
            
            // For normal requests, the layout will show the lock screen.
            view()->share('isAccountRestricted', true);
            view()->share('restrictionStatus', $user->status);
        }

        return $next($request);
    }
}
