<?php

namespace App\Listeners;

use App\Events\OrderInstallationQuoteRequested;
use App\Mail\OrderInstallationQuoteRequestedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderInstallationQuoteRequestEmail
{
    public function handle(OrderInstallationQuoteRequested $event): void
    {
        $order = $event->order->loadMissing('client');
        $client = $order->client;
        if (! $client?->login_email) {
            Log::warning('Installation quote request email skipped: client has no login_email', ['order_id' => $order->id]);

            return;
        }

        $locale = MailLocale::resolve();
        Mail::to($client->login_email)->locale($locale)->send(new OrderInstallationQuoteRequestedMail($order));
    }

    public function failed(OrderInstallationQuoteRequested $event, \Throwable $e): void
    {
        Log::error('Installation quote request email failed', [
            'order_id' => $event->order->id,
            'message' => $e->getMessage(),
        ]);
    }
}
