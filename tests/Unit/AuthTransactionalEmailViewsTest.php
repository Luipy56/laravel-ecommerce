<?php

namespace Tests\Unit;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AuthTransactionalEmailViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_verify_email_blade_uses_branded_layout(): void
    {
        $html = View::make('emails.auth-verify-email', [
            'actionUrl' => 'https://example.com/verify/1/abc',
            'emailTitle' => 'T',
            'mailLocale' => 'ca',
        ])->render();

        $this->assertStringContainsString('F75211', $html);
        $this->assertStringContainsString('https://example.com/verify/1/abc', $html);
    }

    public function test_auth_reset_password_blade_uses_branded_layout(): void
    {
        $html = View::make('emails.auth-reset-password', [
            'actionUrl' => 'https://example.com/reset?token=x',
            'emailTitle' => 'T',
            'mailLocale' => 'es',
            'expireMinutes' => 60,
        ])->render();

        $this->assertStringContainsString('F75211', $html);
        $this->assertStringContainsString('https://example.com/reset?token=x', $html);
    }

    public function test_frontend_password_reset_url_matches_spa_query_shape(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'u@example.org',
            'password' => bcrypt('x'),
            'is_active' => true,
        ]);
        $url = \App\Support\FrontendPasswordResetUrl::make($client, 'plain-token');
        $this->assertStringContainsString('/reset-password', $url);
        $this->assertStringContainsString('token=plain-token', $url);
        $this->assertStringContainsString('login_email=u%40example.org', $url);
    }
}
