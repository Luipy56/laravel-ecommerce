<?php

namespace App\Listeners;

use App\Events\OrderInstallationQuoteRequested;
use App\Mail\OrderInstallationQuoteRequestedAdminMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderInstallationQuoteRequestAdminEmail
{
    public function handle(OrderInstallationQuoteRequested $event): void
    {
        $adminTo = config('mail.admin_notification_address');
        if (empty($adminTo) || ! filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($adminTo)->send(new OrderInstallationQuoteRequestedAdminMail($event->order));
        } catch (\Throwable $e) {
            Log::error('Admin installation quote request email failed', [
                'order_id' => $event->order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
