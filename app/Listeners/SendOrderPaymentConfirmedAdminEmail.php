<?php

namespace App\Listeners;

use App\Events\OrderPaymentSucceeded;
use App\Mail\OrderPaymentConfirmedAdminMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPaymentConfirmedAdminEmail
{
    public function handle(OrderPaymentSucceeded $event): void
    {
        $adminTo = config('mail.admin_notification_address');
        if (empty($adminTo) || ! filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($adminTo)->send(new OrderPaymentConfirmedAdminMail($event->order));
        } catch (\Throwable $e) {
            Log::error('Admin order payment confirmed email failed', [
                'order_id' => $event->order->id,
                'message'  => $e->getMessage(),
            ]);
        }
    }
}
