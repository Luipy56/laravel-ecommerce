<?php

namespace App\Listeners;

use App\Events\InstallationPriceWasAssigned;
use App\Mail\InstallationPriceAssignedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends synchronously so the client receives the email without running a queue worker.
 * For high traffic, consider queuing again and running php artisan queue:work.
 */
class SendInstallationPriceAssignedEmail
{
    public function handle(InstallationPriceWasAssigned $event): void
    {
        $order = $event->order->loadMissing('client');
        $client = $order->client;
        if (! $client?->login_email) {
            Log::warning('Installation price email skipped: client has no login_email', ['order_id' => $order->id]);

            return;
        }

        $locale = MailLocale::resolve();
        Mail::to($client->login_email)->locale($locale)->send(new InstallationPriceAssignedMail($order));
    }

    public function failed(InstallationPriceWasAssigned $event, \Throwable $e): void
    {
        Log::error('Installation price email failed', [
            'order_id' => $event->order->id,
            'message' => $e->getMessage(),
        ]);
    }
}
