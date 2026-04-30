<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeCheckoutConfirmApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_checkout_confirm_requires_authentication(): void
    {
        $this->postJson('/api/v1/payments/stripe/checkout/confirm', [
            'session_id' => 'cs_test_123',
        ])->assertUnauthorized();
    }
}
