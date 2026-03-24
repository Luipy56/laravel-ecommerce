<?php

namespace App\Contracts\Payments;

use App\Models\Payment;

/** Starts a PSP checkout for a pending payment row (client_secret, redirect form, etc.). */
interface PaymentCheckoutStarter
{
    /** @return array<string, mixed> */
    public function start(Payment $payment): array;

    public function gateway(): string;
}
