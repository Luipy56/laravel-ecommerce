<?php

namespace App\Listeners;

use App\Events\ReturnRequestRejectedEvent;
use App\Mail\ReturnRequestRejectedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReturnRequestRejectedEmail
{
    public function handle(ReturnRequestRejectedEvent $event): void
    {
        $rma = $event->returnRequest->loadMissing('order.client');
        $client = $rma->order?->client;
        if (! $client?->login_email) {
            return;
        }

        try {
            $locale = MailLocale::resolve();
            Mail::to($client->login_email)->locale($locale)->send(new ReturnRequestRejectedMail($rma));
        } catch (\Throwable $e) {
            Log::error('Return request rejected email failed', [
                'rma_id' => $rma->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
