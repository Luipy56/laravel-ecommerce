<?php

namespace App\Listeners;

use App\Events\InstallationPriceWasAssigned;
use App\Mail\InstallationPriceAssignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInstallationPriceAssignedEmail implements ShouldQueue
{
    public function handle(InstallationPriceWasAssigned $event): void
    {
        $order = $event->order->loadMissing('client');
        $client = $order->client;
        if (! $client?->login_email) {
            Log::warning('Installation price email skipped: client has no login_email', ['order_id' => $order->id]);

            return;
        }

        $locale = in_array(app()->getLocale(), ['ca', 'es'], true) ? app()->getLocale() : 'ca';
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
