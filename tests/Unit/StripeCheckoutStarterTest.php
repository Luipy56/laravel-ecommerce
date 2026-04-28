<?php

namespace Tests\Unit;

use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Payment;
use App\Services\Payments\Stripe\StripeCheckoutStarter;
use Tests\TestCase;

class StripeCheckoutStarterTest extends TestCase
{
    public function test_start_throws_payment_provider_not_configured_when_secret_empty(): void
    {
        config([
            'services.stripe.secret' => '',
            'services.stripe.key' => 'pk_test_123',
        ]);
        $starter = new StripeCheckoutStarter;
        $payment = new Payment(['payment_method' => Payment::METHOD_CARD]);
        $this->expectException(PaymentProviderNotConfiguredException::class);
        $starter->start($payment);
    }
}
