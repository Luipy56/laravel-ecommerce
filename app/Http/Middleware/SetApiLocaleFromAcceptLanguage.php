<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets app locale from Accept-Language for JSON API responses (validation messages, etc.).
 */
class SetApiLocaleFromAcceptLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $allowed = config('app.available_locales', ['ca', 'es', 'en']);
            $pref = $request->header('Accept-Language', '');
            if (preg_match('/^(ca|es|en)([-_;]|$)/i', $pref, $m)) {
                $loc = strtolower($m[1]);
                if (in_array($loc, $allowed, true)) {
                    app()->setLocale($loc);
                }
            }
        }

        return $next($request);
    }
}
