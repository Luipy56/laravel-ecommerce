<?php

namespace App\Listeners;

use App\Events\PersonalizedSolutionSubmitted;
use App\Mail\PersonalizedSolutionReceivedMail;
use App\Support\MailLocale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPersonalizedSolutionAcknowledgementEmail
{
    public function handle(PersonalizedSolutionSubmitted $event): void
    {
        $solution = $event->solution;
        $email = $solution->email;
        if ($email === null || $email === '') {
            Log::warning('Personalized solution acknowledgement email skipped: no email', ['solution_id' => $solution->id]);

            return;
        }

        $locale = MailLocale::resolve($event->locale);
        Mail::to($email)->locale($locale)->send(new PersonalizedSolutionReceivedMail($solution));
    }

    public function failed(PersonalizedSolutionSubmitted $event, \Throwable $e): void
    {
        Log::error('Personalized solution acknowledgement email failed', [
            'solution_id' => $event->solution->id,
            'message' => $e->getMessage(),
        ]);
    }
}
