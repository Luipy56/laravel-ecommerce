<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ClientEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_register_dispatches_verify_email_notification(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/register', [
            'type' => 'person',
            'identification' => null,
            'login_email' => 'newuser_'.uniqid('', true).'@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'name' => 'Test',
            'surname' => 'User',
            'phone' => null,
            'address_street' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => '08001',
        ]);

        $response->assertCreated();
        $client = Client::query()->where('login_email', $response->json('data.login_email'))->firstOrFail();

        Notification::assertSentTo($client, VerifyEmail::class);
    }

    public function test_orders_list_returns_403_until_email_verified(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'pending_'.uniqid('', true).'@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $this->actingAs($client, 'web')
            ->getJson('/api/v1/orders')
            ->assertStatus(403)
            ->assertJsonPath('code', 'email_not_verified');
    }

    public function test_signed_verify_link_sets_email_verified_at(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'verifyme_'.uniqid('', true).'@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $client->id,
                'hash' => sha1($client->getEmailForVerification()),
            ],
        );

        $response = $this->get($url);
        $response->assertRedirect();

        $client->refresh();
        $this->assertNotNull($client->email_verified_at);
        $this->assertTrue($client->hasVerifiedEmail());
    }
}
