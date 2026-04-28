<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaymentPendingMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['lines', 'client', 'addresses', 'payments']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.order_payment_pending.subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-payment-pending',
            with: [
                'order' => $this->order,
                'grandTotal' => $this->order->grand_total,
            ],
        );
    }
}
