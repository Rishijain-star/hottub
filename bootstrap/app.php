<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware([])
                ->group(base_path('routes/storage-public.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\TrackCustomerActivity::class,
            \App\Http\Middleware\EnsureSessionMatchesPassword::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'full.admin' => \App\Http\Middleware\FullAdminMiddleware::class,
            'dealer' => \App\Http\Middleware\DealerMiddleware::class,
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'phone.verified' => \App\Http\Middleware\EnsurePhoneVerified::class,
            'manufacturer' => \App\Http\Middleware\ManufacturerMiddleware::class,
            'overdue' => \App\Http\Middleware\CheckOverdueRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
