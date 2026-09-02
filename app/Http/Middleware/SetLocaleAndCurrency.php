<?php

namespace App\Http\Middleware;

use App\Services\LocalizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleAndCurrency
{
    public function __construct(
        protected LocalizationService $localization,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('location-preference') || $request->is('locale-preference')) {
            return $next($request);
        }

        $this->localization->applyForRequest(Auth::user());

        return $next($request);
    }
}
