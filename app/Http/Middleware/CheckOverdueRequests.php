<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOverdueRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requests = $request->route()->parameter('requests');

        if ($requests) {
            foreach ($requests as $request) {
                if ($request->status === 'pending' && $request->created_at->diffInHours(now()) > 3) {
                    $request->overdue = true;
                }
            }
        }

        return $next($request);
    }
}