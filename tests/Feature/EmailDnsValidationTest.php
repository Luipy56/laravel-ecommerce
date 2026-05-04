<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ensures transactional-email addresses are validated with RFC + DNS (MX / mail-capable domain).
 */
class EmailDnsValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_register_rejects_email_domain_without_mail_dns(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'type' => 'person',
            'identification' => null,
            'login_email' => 'nope_'.uniqid('', true).'@invalid.invalid',
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

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login_email']);
    }

    public function test_forgot_password_rejects_email_domain_without_mail_dns(): void
    {
        $this->postJson('/api/v1/forgot-password', [
            'login_email' => 'nobody@invalid.invalid',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['login_email']);
    }

    public function test_personalized_solution_rejects_email_domain_without_mail_dns(): void
    {
        $this->postJson('/api/v1/personalized-solutions', [
            'email' => 'nobody@invalid.invalid',
            'problem_description' => 'Need help',
            'address_postal_code' => '08001',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
