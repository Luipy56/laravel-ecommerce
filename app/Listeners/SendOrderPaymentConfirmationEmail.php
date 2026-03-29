<?php

namespace App\Listeners;

use App\Events\OrderPaymentSucceeded;
use App\Mail\OrderPaymentConfirmedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends synchronously so the message is attempted in the same request as payment completion.
 */
class SendOrderPaymentConfirmationEmail
{
    public function handle(OrderPaymentSucceeded $event): void
    {
        $order = $event->order->loadMissing('client');
        $client = $order->client;
        if (! $client?->login_email) {
            Log::warning('Order payment confirmation email skipped: client has no login_email', ['order_id' => $order->id]);

            return;
        }

        $locale = MailLocale::resolve();
        Mail::to($client->login_email)->locale($locale)->send(new OrderPaymentConfirmedMail($order));
    }

    public function failed(OrderPaymentSucceeded $event, \Throwable $e): void
    {
        Log::error('Order payment confirmation email failed', [
            'order_id' => $event->order->id,
            'message' => $e->getMessage(),
        ]);
    }
}
