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

    public function __construct(public PersonalizedSolution $solution)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.personalized_solution_resolved.subject', ['id' => $this->solution->id]),
        );
    }

    public function content(): Content
    {
        $portalUrl = $this->solution->public_token ? $this->solution->portalUrl() : url('/');

        return new Content(
            html: 'emails.personalized-solution-resolved',
            with: [
                'solution' => $this->solution,
                'portalUrl' => $portalUrl,
                'managePreferencesUrl' => $portalUrl,
            ],
        );
    }
}
