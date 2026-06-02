<?php

namespace App\Services\Auth;

use App\Exceptions\GoogleOAuthException;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleOAuthService
{
    /**
     * Find, link, or create a storefront client from a Google OIDC user payload.
     */
    public function resolveClientFromGoogleUser(
        SocialiteUser $googleUser,
        bool $acceptMarketing,
        string $ip,
        ?string $userAgent,
    ): Client {
        $sub = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($sub === '' || $email === '') {
            throw new GoogleOAuthException('provider_error');
        }

        $raw = $googleUser->getRaw();
        $emailVerified = (bool) ($raw['email_verified'] ?? false);
        $givenName = trim((string) ($raw['given_name'] ?? ''));
        $familyName = trim((string) ($raw['family_name'] ?? ''));
        if ($givenName === '' && $googleUser->getName()) {
            $parts = preg_split('/\s+/', trim((string) $googleUser->getName()), 2) ?: [];
            $givenName = $parts[0] ?? '';
            $familyName = $familyName !== '' ? $familyName : ($parts[1] ?? '');
        }

        $bySub = Client::query()->where('google_sub', $sub)->first();
        $byEmail = Client::query()->where('login_email', $email)->first();

        if ($bySub && $byEmail && $bySub->id !== $byEmail->id) {
            Log::warning('google_oauth_sub_email_mismatch', [
                'google_sub' => $sub,
                'email' => $email,
                'sub_client_id' => $bySub->id,
                'email_client_id' => $byEmail->id,
            ]);

            throw new GoogleOAuthException('sub_conflict');
        }

        if ($bySub) {
            $client = $bySub;
            $this->ensureEmailMatches($client, $email);
            $this->applyEmailVerification($client, $emailVerified);
            $this->recordConsents($client, $acceptMarketing, $ip, $userAgent);

            return $client;
        }

        if ($byEmail) {
            if (! $emailVerified) {
                throw new GoogleOAuthException('email_not_verified');
            }

            if ($byEmail->google_sub !== null && $byEmail->google_sub !== $sub) {
                Log::warning('google_oauth_sub_conflict', [
                    'google_sub' => $sub,
                    'existing_sub' => $byEmail->google_sub,
                    'client_id' => $byEmail->id,
                ]);

                throw new GoogleOAuthException('sub_conflict');
            }

            $byEmail->update(['google_sub' => $sub]);
            $this->applyEmailVerification($byEmail, $emailVerified);
            $this->recordConsents($byEmail, $acceptMarketing, $ip, $userAgent);

            return $byEmail->fresh();
        }

        return DB::transaction(function () use ($sub, $email, $emailVerified, $givenName, $familyName, $acceptMarketing, $ip, $userAgent) {
            $client = Client::create([
                'type' => 'person',
                'identification' => null,
                'login_email' => $email,
                'password' => null,
                'google_sub' => $sub,
                'is_active' => true,
                'email_verified_at' => $emailVerified ? now() : null,
            ]);

            $client->contacts()->create([
                'name' => $givenName !== '' ? $givenName : $email,
                'surname' => $familyName !== '' ? $familyName : null,
                'phone' => null,
                'email' => $email,
                'is_primary' => true,
                'is_active' => true,
            ]);

            $this->recordConsents($client, $acceptMarketing, $ip, $userAgent);

            return $client;
        });
    }

    public function isProfileIncomplete(Client $client): bool
    {
        $client->load([
            'contacts' => fn ($q) => $q->where('is_primary', true),
            'addresses' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_primary')->orderBy('id'),
        ]);

        $primary = $client->contacts->first();
        $phone = trim((string) ($primary?->phone ?? ''));
        $address = $client->addresses->first();
        $postal = trim((string) ($address?->postal_code ?? ''));

        return $phone === '' || $postal === '';
    }

    private function ensureEmailMatches(Client $client, string $email): void
    {
        if (strtolower((string) $client->login_email) !== $email) {
            Log::warning('google_oauth_email_mismatch_for_sub', [
                'client_id' => $client->id,
                'google_sub' => $client->google_sub,
                'expected' => $client->login_email,
                'received' => $email,
            ]);

            throw new GoogleOAuthException('sub_conflict');
        }
    }

    private function applyEmailVerification(Client $client, bool $emailVerified): void
    {
        if ($emailVerified && ! $client->hasVerifiedEmail()) {
            $client->forceFill(['email_verified_at' => now()])->save();
        }
    }

    private function recordConsents(Client $client, bool $acceptMarketing, string $ip, ?string $userAgent): void
    {
        $policyVersion = config('app.privacy_policy_version', '2026-05-05');

        if (! $client->consents()->where('type', 'privacy_policy')->where('version', $policyVersion)->exists()) {
            $client->consents()->create([
                'type' => 'privacy_policy',
                'version' => $policyVersion,
                'accepted' => true,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        }

        if ($acceptMarketing && ! $client->consents()->where('type', 'marketing')->where('version', $policyVersion)->exists()) {
            $client->consents()->create([
                'type' => 'marketing',
                'version' => $policyVersion,
                'accepted' => true,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        }
    }
}
