<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks storefront API routes until the authenticated client has verified login_email.
 */
class EnsureClientEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user();
        if ($client === null) {
            return $next($request);
        }

        if ($client->hasVerifiedEmail()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.verify_email_required'),
            'code' => 'email_not_verified',
        ], 403);
    }
}
