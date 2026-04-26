<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInstallationQuoteRequestedAdminMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['client', 'lines', 'addresses']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.admin_order_installation_quote.subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-installation-quote-requested-admin',
            with: [
                'order' => $this->order,
                'adminUrl' => url('/admin/orders/'.$this->order->id),
            ],
        );
    }
}
