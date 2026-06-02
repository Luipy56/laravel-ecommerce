<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\ClientConsent;
use App\Services\Auth\GoogleOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleOAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_marketing_consent_when_opted_in(): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('google-sub-mkt-unit');
        $googleUser->shouldReceive('getEmail')->andReturn('google_mkt_unit@ietf.org');
        $googleUser->shouldReceive('getName')->andReturn('Marketing User');
        $googleUser->shouldReceive('getRaw')->andReturn([
            'email_verified' => true,
            'given_name' => 'Marketing',
            'family_name' => 'User',
        ]);

        $service = new GoogleOAuthService;
        $client = $service->resolveClientFromGoogleUser($googleUser, true, '127.0.0.1', 'PHPUnit');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame(2, ClientConsent::query()->where('client_id', $client->id)->count());
        $this->assertTrue(
            ClientConsent::query()
                ->where('client_id', $client->id)
                ->where('type', 'marketing')
                ->exists()
        );
    }
}
