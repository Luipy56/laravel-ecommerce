<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Checkout completed but no successful payment in this request (card redirect, PayPal, or start error).
 */
class OrderPlacedPaymentPending
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public string $mailLocale = 'ca',
    ) {}
}
