<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class CsrfRecoveryTest extends TestCase
{
    use RefreshDatabase;

    /** @var string|null Previous app env while CSRF assertions run with enforcement enabled. */
    private ?string $previousAppEnv = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Laravel skips CSRF when env=testing (VerifyCsrfToken::runningUnitTests). Force enforcement here.
        $this->previousAppEnv = $this->app->environment();
        $this->app['env'] = 'local';
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->previousAppEnv !== null) {
            $this->app['env'] = $this->previousAppEnv;
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_csrf_cookie_endpoint_returns_fresh_token(): void
    {
        $this->get('/csrf-cookie')
            ->assertOk()
            ->assertJsonStructure(['token'])
            ->assertCookie('XSRF-TOKEN');
    }

    public function test_csrf_ping_returns_no_content(): void
    {
        $this->getJson('/api/v1/csrf-ping')
            ->assertNoContent();
    }

    public function test_google_redirect_without_csrf_token_returns_419(): void
    {
        // Do not warm /csrf-cookie first: the test client replays XSRF-TOKEN as X-XSRF-TOKEN on POST,
        // which would make this assertion environment-dependent (302 vs 419).
        $this->post('/auth/google/redirect', [])
            ->assertStatus(419);
    }

    public function test_google_redirect_with_fresh_csrf_token_is_accepted(): void
    {
        $token = $this->get('/csrf-cookie')->json('token');
        $this->assertNotEmpty($token);

        $this->fakeGoogleSocialiteUser('csrf-sub-'.uniqid('', true), 'csrf_'.uniqid('', true).'@ietf.org', true);

        $this->post('/auth/google/redirect', ['_token' => $token])
            ->assertRedirect();
    }

    public function test_token_mismatch_is_logged_without_token_values(): void
    {
        Log::spy();

        $this->post('/auth/google/redirect', ['_token' => 'stale-or-invalid-token'])
            ->assertStatus(419);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'CSRF token mismatch'
                    && isset($context['path'], $context['method'], $context['has_session_cookie'])
                    && ! isset($context['token'], $context['_token']);
            });
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
