<?php

namespace App\Listeners;

use App\Events\ReturnRequestApprovedEvent;
use App\Mail\ReturnRequestApprovedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReturnRequestApprovedEmail
{
    public function handle(ReturnRequestApprovedEvent $event): void
    {
        $rma = $event->returnRequest->loadMissing('order.client');
        $client = $rma->order?->client;
        if (! $client?->login_email) {
            return;
        }

        try {
            $locale = MailLocale::resolve();
            Mail::to($client->login_email)->locale($locale)->send(new ReturnRequestApprovedMail($rma));
        } catch (\Throwable $e) {
            Log::error('Return request approved email failed', [
                'rma_id' => $rma->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
