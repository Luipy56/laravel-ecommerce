<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  string|null  $deliveryEstimateKey  When set: today, few_days, or soon (friendly copy for “unknown” ETA).
     */
    public function __construct(
        public Order $order,
        public ?string $deliveryEstimateKey = null,
    ) {
        $this->order->loadMissing(['lines', 'client', 'addresses']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.order_shipped.subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-shipped',
            with: [
                'order' => $this->order,
                'shippingDate' => $this->order->shipping_date,
                'deliveryEstimateKey' => $this->deliveryEstimateKey,
            ],
        );
    }
}
