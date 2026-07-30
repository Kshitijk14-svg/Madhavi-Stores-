<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRefundRequiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $reason;
    public bool $refundSucceeded;

    public function __construct(Order $order, string $reason, bool $refundSucceeded)
    {
        $this->order           = $order;
        $this->reason          = $reason;
        $this->refundSucceeded = $refundSucceeded;
    }

    public function envelope(): Envelope
    {
        $prefix = $this->refundSucceeded ? 'Order Cancelled & Refunded' : 'ACTION NEEDED — Refund Failed';

        return new Envelope(
            subject: $prefix . ' — Order #' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-refund-required',
            with: [
                'order'           => $this->order,
                'reason'          => $this->reason,
                'refundSucceeded' => $this->refundSucceeded,
            ],
        );
    }
}
