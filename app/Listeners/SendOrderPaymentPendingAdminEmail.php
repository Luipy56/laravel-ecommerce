<?php

namespace App\Listeners;

use App\Events\OrderPlacedPaymentPending;
use App\Mail\OrderPaymentPendingAdminMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPaymentPendingAdminEmail
{
    public function handle(OrderPlacedPaymentPending $event): void
    {
        $adminTo = config('mail.admin_notification_address');
        if (empty($adminTo) || ! filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($adminTo)->send(new OrderPaymentPendingAdminMail($event->order));
        } catch (\Throwable $e) {
            Log::error('Admin order payment pending email failed', [
                'order_id' => $event->order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
