<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Mail\OrderShippedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderShippedEmail
{
    public function handle(OrderShipped $event): void
    {
        $order = $event->order->loadMissing('client');
        $client = $order->client;
        if (! $client?->login_email) {
            Log::warning('Order shipped email skipped: client has no login_email', ['order_id' => $order->id]);

            return;
        }

        $locale = MailLocale::resolve();
        Mail::to($client->login_email)->locale($locale)->send(new OrderShippedMail($order));
    }

    public function failed(OrderShipped $event, \Throwable $e): void
    {
        Log::error('Order shipped email failed', [
            'order_id' => $event->order->id,
            'message' => $e->getMessage(),
        ]);
    }
}
