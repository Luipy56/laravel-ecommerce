<?php

namespace Tests\Unit;

use App\Models\Client;
use Tests\TestCase;

class ClientNotificationRoutingTest extends TestCase
{
    public function test_route_notification_for_mail_uses_login_email(): void
    {
        $client = new Client;
        $client->login_email = 'buyer@ietf.org';

        $this->assertSame('buyer@ietf.org', $client->routeNotificationForMail());
    }

    public function test_route_notification_for_mail_returns_null_when_login_email_empty(): void
    {
        $client = new Client;
        $client->login_email = '';

        $this->assertNull($client->routeNotificationForMail());
    }
}
