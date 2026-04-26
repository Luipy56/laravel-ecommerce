<?php

namespace App\Mail;

use App\Models\PersonalizedSolution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PersonalizedSolutionResolvedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public PersonalizedSolution $solution,
        public string $mailLocale = 'ca',
    ) {
        //
    }

    public function envelope(): Envelope
    {
        $subject = (string) trans('mail.personalized_solution_resolved.subject', [
            'id' => $this->solution->id,
        ], $this->mailLocale);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $portalUrl = $this->solution->public_token ? $this->solution->portalUrl() : url('/');
        $emailTitle = (string) trans('mail.personalized_solution_resolved.subject', [
            'id' => $this->solution->id,
        ], $this->mailLocale);

        return new Content(
            html: 'emails.personalized-solution-resolved',
            with: [
                'solution' => $this->solution,
                'portalUrl' => $portalUrl,
                'emailTitle' => $emailTitle,
                'mailLocale' => $this->mailLocale,
            ],
        );
    }
}
