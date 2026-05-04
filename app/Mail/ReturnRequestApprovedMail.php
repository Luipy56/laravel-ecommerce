<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ReturnRequest $returnRequest)
    {
        $this->returnRequest->loadMissing(['order.client', 'order.lines']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.return_request_approved.subject', ['id' => $this->returnRequest->order_id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.return-request-approved',
            with: [
                'rma' => $this->returnRequest,
                'orderUrl' => url('/orders/'.$this->returnRequest->order_id),
            ],
        );
    }
}
