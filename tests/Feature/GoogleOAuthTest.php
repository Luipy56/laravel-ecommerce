<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientConsent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        // Warm HTTP kernel so web POST routes (OAuth redirect) get a session before Socialite mocks.
        $this->getJson('/api/v1/auth/google-config');
    }

    public function test_google_config_endpoint_reports_enabled_when_configured(): void
    {
        $this->getJson('/api/v1/auth/google-config')
            ->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.client_id', 'test-google-client-id');
    }

    public function test_existing_local_user_with_verified_google_email_is_linked(): void
    {
        $email = 'linkme_'.uniqid('', true).'@ietf.org';
        $client = Client::create([
            'type' => 'person',
            'login_email' => $email,
            'password' => bcrypt('Password123!'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $client->contacts()->create([
            'name' => 'Local',
            'surname' => 'User',
            'email' => $email,
            'is_primary' => true,
            'is_active' => true,
        ]);

        $sub = 'google-sub-link-'.uniqid('', true);
        $this->fakeGoogleSocialiteUser($sub, $email, true);

        $this->post('/auth/google/redirect', ['accept_privacy' => '1']);
        $this->get('/auth/google/callback')->assertRedirect();

        $client->refresh();
        $this->assertSame($sub, $client->google_sub);
        $this->assertAuthenticatedAs($client, 'web');
    }

    public function test_google_email_not_verified_does_not_link_existing_account(): void
    {
        $email = 'unverified_'.uniqid('', true).'@ietf.org';
        $client = Client::create([
            'type' => 'person',
            'login_email' => $email,
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);

        $this->fakeGoogleSocialiteUser('sub-unverified', $email, false);

        $this->post('/auth/google/redirect', ['accept_privacy' => '1']);
        $response = $this->get('/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('oauth=error', $response->headers->get('Location'));
        $this->assertStringContainsString('code=email_not_verified', $response->headers->get('Location'));
        $client->refresh();
        $this->assertNull($client->google_sub);
        $this->assertGuest();
    }

    public function test_google_login_sets_email_verified_at_when_provider_verifies_email(): void
    {
        $email = 'verify_google_'.uniqid('', true).'@ietf.org';
        $sub = 'google-sub-verify-'.uniqid('', true);

        $this->fakeGoogleSocialiteUser($sub, $email, true);

        $this->post('/auth/google/redirect', ['accept_privacy' => '1']);
        $this->get('/auth/google/callback');

        $client = Client::query()->where('login_email', $email)->firstOrFail();
        $this->assertNotNull($client->email_verified_at);
        $this->assertNull($client->password);
        $this->assertSame($sub, $client->google_sub);
        $this->assertTrue($client->contacts()->where('is_primary', true)->exists());
        $this->assertGreaterThanOrEqual(1, ClientConsent::query()->where('client_id', $client->id)->where('type', 'privacy_policy')->count());
        $this->assertAuthenticatedAs($client, 'web');
    }

    public function test_password_login_still_works_for_linked_account(): void
    {
        $email = 'linked_pw_'.uniqid('', true).'@ietf.org';
        $password = 'Password123!';
        $client = Client::create([
            'type' => 'person',
            'login_email' => $email,
            'password' => $password,
            'google_sub' => 'google-sub-pw-'.uniqid('', true),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->postJson('/api/v1/login', [
            'login_email' => $email,
            'password' => $password,
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertAuthenticatedAs($client, 'web');
    }

    public function test_google_only_account_cannot_password_login_without_password(): void
    {
        $email = 'google_only_'.uniqid('', true).'@ietf.org';
        Client::create([
            'type' => 'person',
            'login_email' => $email,
            'password' => null,
            'google_sub' => 'google-sub-only',
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/login', [
            'login_email' => $email,
            'password' => 'Password123!',
        ])->assertStatus(422);
    }

    private function fakeGoogleSocialiteUser(string $sub, string $email, bool $emailVerified): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn($sub);
        $abstractUser->shouldReceive('getEmail')->andReturn($email);
        $abstractUser->shouldReceive('getName')->andReturn('Google Test');
        $abstractUser->shouldReceive('getRaw')->andReturn([
            'sub' => $sub,
            'email' => $email,
            'email_verified' => $emailVerified,
            'given_name' => 'Google',
            'family_name' => 'Test',
        ]);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('scopes')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
