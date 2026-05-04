<?php

namespace App\Listeners;

use App\Events\ReturnRequestRefundedEvent;
use App\Mail\ReturnRequestRefundedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReturnRequestRefundedEmail
{
    public function handle(ReturnRequestRefundedEvent $event): void
    {
        $rma = $event->returnRequest->loadMissing('order.client');
        $client = $rma->order?->client;
        if (! $client?->login_email) {
            return;
        }

        try {
            $locale = MailLocale::resolve();
            Mail::to($client->login_email)->locale($locale)->send(new ReturnRequestRefundedMail($rma));
        } catch (\Throwable $e) {
            Log::error('Return request refunded email failed', [
                'rma_id' => $rma->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
