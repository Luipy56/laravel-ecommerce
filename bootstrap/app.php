<?php

use App\Exceptions\PaymentProviderNotConfiguredException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
        $middleware->validateCsrfTokens(except: [
            'api/v1/payments/webhooks/*',
        ]);
        $middleware->alias(['admin' => \App\Http\Middleware\EnsureAdmin::class]);
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
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*');
        });
    })->create();
