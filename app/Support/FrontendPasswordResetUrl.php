<?php

namespace App\Support;

use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * SPA reset-password URL (same shape as {@see ResetPassword::createUrlUsing} in AppServiceProvider).
 */
final class FrontendPasswordResetUrl
{
    public static function make(CanResetPasswordContract $user, #[\SensitiveParameter] string $token): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $path = config('app.frontend_reset_password_path', '/reset-password');
        $email = urlencode($user->getEmailForPasswordReset());

        return $base.$path.'?token='.urlencode($token).'&login_email='.$email;
    }
}
