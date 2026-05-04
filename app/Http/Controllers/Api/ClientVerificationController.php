<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\MailLocale;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientVerificationController extends Controller
{
    /**
     * Signed link from the verification email; marks the client verified and redirects to the SPA.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $client = Client::query()->findOrFail($id);

        if (! hash_equals((string) $hash, sha1($client->getEmailForVerification()))) {
            abort(403);
        }

        if ($client->hasVerifiedEmail()) {
            return $this->verifiedRedirect();
        }

        if ($client->markEmailAsVerified()) {
            event(new Verified($client));
        }

        return $this->verifiedRedirect();
    }

    /**
     * Resend verification email for the logged-in client.
     */
    public function resend(Request $request): JsonResponse
    {
        $client = $request->user();
        if ($client->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => __('auth.email_already_verified'),
            ]);
        }

        $locale = MailLocale::resolve($request->getPreferredLanguage(config('app.available_locales', ['ca', 'es', 'en'])));
        app()->setLocale($locale);

        $client->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => __('auth.verification_link_sent'),
        ]);
    }

    private function verifiedRedirect(): RedirectResponse
    {
        $path = config('app.verify_email_redirect_path', '/login');
        $base = rtrim((string) config('app.url'), '/');
        $join = str_contains($path, '?') ? '&' : '?';

        return redirect()->away($base.$path.$join.'verified=1');
    }
}
