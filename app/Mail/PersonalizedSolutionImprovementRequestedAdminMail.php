<?php

namespace App\Mail;

use App\Models\PersonalizedSolution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PersonalizedSolutionImprovementRequestedAdminMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public PersonalizedSolution $solution,
        public string $clientMessage,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.admin_personalized_improvement.subject', ['id' => $this->solution->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.personalized-solution-improvement-admin',
            with: [
                'solution' => $this->solution,
                'message' => $this->clientMessage,
                'adminUrl' => url('/admin/personalized-solutions/'.$this->solution->id),
            ],
        );
    }
}
