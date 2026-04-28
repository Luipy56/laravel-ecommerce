<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows storefront (`web` guard / Client) or admin (`admin` guard / Admin).
 */
class AuthenticateClientOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('admin') !== null || $request->user() !== null) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthenticated'),
        ], 401);
    }
}
