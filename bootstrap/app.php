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
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\EnsureDeviceFingerprint::class,
            \App\Http\Middleware\SetLocaleAndCurrency::class,
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
            'admin.2fa' => \App\Http\Middleware\RequireAdminTwoFactor::class,
            'verify.otp.captcha' => \App\Http\Middleware\VerifyOtpCaptcha::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            $recovery = app(\App\Services\RegistrationFlowRecovery::class);

            if ($recovery->isRegistrationRequest($request) || $request->session()->has(\App\Services\RegistrationFlowRecovery::SESSION_KEY)) {
                return $recovery->redirectAfterTokenMismatch($request);
            }

            if ($request->routeIs('login')) {
                return redirect()
                    ->route('login')
                    ->with('show_toast', true)
                    ->with('error', 'Your session expired. Please log in again.');
            }

            if ($request->routeIs('password.*')) {
                return redirect()
                    ->route('password.request')
                    ->with('show_toast', true)
                    ->with('error', 'Your session expired. Please request a new code.');
            }

            return redirect()
                ->route('home')
                ->with('show_toast', true)
                ->with('error', 'Your session expired. Please refresh the page and try again.');
        });

        $exceptions->renderable(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The uploaded file is too large. Please reduce the file size and try again.',
                ], 413);
            }
            return back()->withErrors([
                'file' => 'The uploaded file is too large. Please reduce the file size and try again.',
            ]);
        });
    })
    ->create();
