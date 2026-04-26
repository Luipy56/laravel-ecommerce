<?php

namespace App\Listeners;

use App\Events\OrderPlacedPaymentPending;
use App\Mail\OrderPaymentPendingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPaymentPendingEmail
{
    public function handle(OrderPlacedPaymentPending $event): void
    {
        $order = $event->order->loadMissing('client');
        $client = $order->client;
        if (! $client?->login_email) {
            Log::warning('Order payment pending email skipped: client has no login_email', ['order_id' => $order->id]);

            return;
        }

        Mail::to($client->login_email)->locale($event->mailLocale)->send(new OrderPaymentPendingMail($order));
    }

    public function failed(OrderPlacedPaymentPending $event, \Throwable $e): void
    {
        Log::error('Order payment pending email failed', [
            'order_id' => $event->order->id,
            'message' => $e->getMessage(),
        ]);
    }
}
