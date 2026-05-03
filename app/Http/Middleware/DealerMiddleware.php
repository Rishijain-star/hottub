<?php

namespace App\Http\Middleware;
// app/Http/Middleware/DealerMiddleware.php

use Closure;
use Illuminate\Http\Request;

class DealerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isDealer()) {
            abort(403, 'Access denied. Dealers only.');
        }

        $user = \App\Models\User::find(auth()->id());

        if ($user && $user->status === 'pending') {
            if ($request->routeIs('dealer.account.pending')) {
                return $next($request);
            }

            return redirect()->route('dealer.account.pending');
        }

        if ($user && ($user->status === 'paused' || $user->status === 'frozen')) {
            $isSendMessageToAdmin = $request->is('dealer/api/messages/1') && $request->isMethod('POST');

            if (!$isSendMessageToAdmin) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'msg' => 'Your account is ' . $user->status . '. Please contact support.',
                        'restricted' => true
                    ], 403);
                }

                view()->share('isAccountRestricted', true);
                view()->share('restrictionStatus', $user->status);
            }
        }

        return $next($request);
    }
}
