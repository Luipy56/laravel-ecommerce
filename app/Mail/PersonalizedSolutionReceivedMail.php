<?php

namespace App\Mail;

use App\Models\PersonalizedSolution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PersonalizedSolutionReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public PersonalizedSolution $solution)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.personalized_solution.subject', ['id' => $this->solution->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.personalized-solution-received',
            with: [
                'solution' => $this->solution,
            ],
        );
    }
}
