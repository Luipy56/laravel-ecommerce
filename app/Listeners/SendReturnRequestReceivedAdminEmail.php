<?php

namespace App\Listeners;

use App\Events\ReturnRequestCreated;
use App\Mail\ReturnRequestReceivedAdminMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReturnRequestReceivedAdminEmail
{
    public function handle(ReturnRequestCreated $event): void
    {
        $adminTo = config('mail.admin_notification_address');
        if (empty($adminTo) || ! filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($adminTo)->send(new ReturnRequestReceivedAdminMail($event->returnRequest));
        } catch (\Throwable $e) {
            Log::error('Return request received admin email failed', [
                'rma_id' => $event->returnRequest->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
