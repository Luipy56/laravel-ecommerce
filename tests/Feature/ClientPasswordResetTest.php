<?php

namespace Tests\Feature;

use App\Models\Client;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ClientPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_reset_password_succeeds_with_token_and_login_email(): void
    {
        $this->seed(DatabaseSeeder::class);

        $client = Client::query()->first();
        $this->assertNotNull($client);

        // Seeded @example.com addresses use null-MX; broker token must match stored login_email.
        $client->forceFill(['login_email' => 'pw-reset-test@gmail.com'])->save();

        $token = Password::broker('clients')->createToken($client);
        $newPassword = 'NewSecurePass456!';

        $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'login_email' => 'pw-reset-test@gmail.com',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check($newPassword, $client->fresh()->password));
    }
}
