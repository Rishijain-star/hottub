<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ManufacturerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isManufacturer()) {
            abort(403, 'Access denied. Manufacturers only.');
        }

        // Freshly check status from DB to ensure real-time locking
        $user = \App\Models\User::find(auth()->id());
        if ($user && ($user->status === 'paused' || $user->status === 'frozen')) {
            // Allow sending support messages to admin (receiver_id 1) even if restricted
            $isSendMessageToAdmin = $request->is('manufacturer/api/messages/1') && $request->isMethod('POST');
            
            if (!$isSendMessageToAdmin) {
                if ($request->expectsJson() || $request->ajax()) {
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

