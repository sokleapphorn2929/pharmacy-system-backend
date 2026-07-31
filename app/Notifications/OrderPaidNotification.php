<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Order Successful - Payment Paid',
            'message' => 'Your payment has been verified by the admin. Order #' . $this->order->id . ' is successfully processed.',
            'order_id' => $this->order->id,
            'status' => 'paid',
        ];
    }
}