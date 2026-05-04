<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaymentConfirmedAdminMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['client', 'lines', 'addresses', 'payments']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.admin_order_payment_confirmed.subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-payment-confirmed-admin',
            with: [
                'order'      => $this->order,
                'grandTotal' => $this->order->grand_total,
                'adminUrl'   => url('/admin/orders/' . $this->order->id),
            ],
        );
    }
}
