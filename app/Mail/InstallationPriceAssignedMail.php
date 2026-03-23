<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallationPriceAssignedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['lines', 'client']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.installation_price.subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.installation-price-assigned',
            with: [
                'order' => $this->order,
                'linesSubtotal' => $this->order->lines_subtotal,
                'installationPrice' => (float) $this->order->installation_price,
                'grandTotal' => $this->order->grand_total,
            ],
        );
    }
}
