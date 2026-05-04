<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestReceivedAdminMail extends Mailable
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
            subject: __('mail.admin_return_request_received.subject', ['id' => $this->returnRequest->order_id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.return-request-received-admin',
            with: [
                'rma' => $this->returnRequest,
                'adminUrl' => url('/admin/returns/'.$this->returnRequest->id),
            ],
        );
    }
}
