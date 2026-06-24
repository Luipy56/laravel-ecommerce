<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\GoogleOAuthException;
use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleOAuthService;
use App\Support\Auth\GoogleOAuthErrorMapper;
use App\Support\MailLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly GoogleOAuthService $googleOAuth,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! filled(config('services.google.client_id'))) {
            abort(404);
        }

        // Privacy policy acceptance is implicit when the user chooses Google sign-in (see auth.google_privacy_notice_* i18n).
        $request->validate([
            'accept_marketing' => ['nullable', 'in:0,1'],
        ]);

        $marketing = in_array($request->input('accept_marketing'), [1, '1', true, 'true', 'on'], true);

        $request->session()->put('google_oauth', [
            'accept_marketing' => $marketing,
            'next' => $this->safeNext($request->input('next')),
        ]);

        return $this->googleDriver()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $oauthSession = $request->session()->pull('google_oauth', []);
        $next = $this->safeNext($oauthSession['next'] ?? null);

        try {
            $googleUser = $this->googleDriver()->user();
            $client = $this->googleOAuth->resolveClientFromGoogleUser(
                $googleUser,
                (bool) ($oauthSession['accept_marketing'] ?? false),
                (string) $request->ip(),
                $request->userAgent(),
            );

            Auth::login($client);
            $request->session()->regenerate();

            if ($client->wasRecentlyCreated) {
                $locale = MailLocale::resolve($request->getPreferredLanguage(config('app.available_locales', ['ca', 'es', 'en'])));
                app()->setLocale($locale);
                $client->sendEmailVerificationNotification();
            }

            $query = http_build_query([
                'oauth' => 'success',
                'next' => $next,
                'profile_incomplete' => $this->googleOAuth->isProfileIncomplete($client) ? '1' : '0',
            ]);

            return redirect('/login?'.$query);
        } catch (GoogleOAuthException $e) {
            return $this->errorRedirect($e->errorCode, $next);
        } catch (Throwable $e) {
            $code = GoogleOAuthErrorMapper::errorCodeFrom($e);
            Log::warning('google_oauth_callback_failed', [
                'code' => $code,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return $this->errorRedirect($code, $next);
        }
    }

    private function googleDriver()
    {
        $driver = Socialite::driver('google');
        $redirect = config('services.google.redirect');
        if (! filled($redirect)) {
            $redirect = rtrim((string) config('app.url'), '/').'/auth/google/callback';
        }

        return $driver->redirectUrl($redirect);
    }

    private function errorRedirect(string $code, string $next): RedirectResponse
    {
        $query = http_build_query([
            'oauth' => 'error',
            'code' => $code,
            'next' => $next,
        ]);

        return redirect('/login?'.$query);
    }

    private function safeNext(mixed $next): string
    {
        if (! is_string($next) || $next === '' || ! str_starts_with($next, '/') || str_starts_with($next, '//')) {
            return '/';
        }

        return $next;
    }
}
