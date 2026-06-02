<?php

use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Http\Controllers\Api\ClientVerificationController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    // Explicit listeners live in AppServiceProvider; automatic discovery from app/Listeners
    // would register the same class again and run each handle() twice.
    ->withEvents(discover: false)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SetApiLocaleFromAcceptLanguage::class);
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'client.verified' => \App\Http\Middleware\EnsureClientEmailVerified::class,
            'auth.client_or_admin' => \App\Http\Middleware\AuthenticateClientOrAdmin::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            PaymentProviderNotConfiguredException::class,
        ]);
        $exceptions->stopIgnoring(TokenMismatchException::class);
        $exceptions->reportable(function (TokenMismatchException $e): void {
            $request = request();
            Log::warning('CSRF token mismatch', [
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_session_cookie' => $request->hasCookie(config('session.cookie')),
            ]);
        });
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*')
                && ! ($e instanceof InvalidSignatureException && $request->routeIs('verification.verify'));
        });
        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->routeIs('verification.verify')) {
                return ClientVerificationController::failedRedirect('expired');
            }

            return null;
        });
    })->create();
