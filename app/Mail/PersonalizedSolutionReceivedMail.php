<?php

namespace App\Mail;

use App\Models\PersonalizedSolution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PersonalizedSolutionReceivedMail extends Mailable
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
        $subject = (string) trans('mail.personalized_solution.subject', [
            'id' => $this->solution->id,
        ], $this->mailLocale);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $portalUrl = $this->solution->public_token ? $this->solution->portalUrl() : url('/custom-solution');
        $emailTitle = (string) trans('mail.personalized_solution.subject', [
            'id' => $this->solution->id,
        ], $this->mailLocale);

        $rawDescription = (string) ($this->solution->problem_description ?? '');
        $maxDescriptionChars = 4000;
        $descriptionTruncated = Str::length($rawDescription) > $maxDescriptionChars;
        $problemPreview = $descriptionTruncated
            ? Str::limit($rawDescription, $maxDescriptionChars, '…')
            : $rawDescription;

        $addressLines = $this->previewAddressLines($this->solution);
        $attachmentFilenames = $this->solution->relationLoaded('attachments')
            ? $this->solution->attachments
                ->pluck('original_filename')
                ->filter(fn (?string $n) => $n !== null && $n !== '')
                ->values()
                ->all()
            : [];

        return new Content(
            html: 'emails.personalized-solution-received',
            with: [
                'solution' => $this->solution,
                'portalUrl' => $portalUrl,
                'emailTitle' => $emailTitle,
                'mailLocale' => $this->mailLocale,
                'problemPreview' => $problemPreview,
                'descriptionTruncated' => $descriptionTruncated,
                'addressLines' => $addressLines,
                'attachmentFilenames' => $attachmentFilenames,
            ],
        );
    }

    /**
     * @return list<string>
     */
    private function previewAddressLines(PersonalizedSolution $solution): array
    {
        $lines = [];
        if ($solution->address_street !== null && $solution->address_street !== '') {
            $lines[] = $solution->address_street;
        }
        $cityLine = trim(implode(' ', array_filter([
            $solution->address_postal_code,
            $solution->address_city,
            $solution->address_province,
        ], fn ($v) => $v !== null && $v !== '')));
        if ($cityLine !== '') {
            $lines[] = $cityLine;
        }
        if ($solution->address_note !== null && $solution->address_note !== '') {
            $lines[] = $solution->address_note;
        }

        return $lines;
    }
}
