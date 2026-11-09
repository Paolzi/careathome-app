<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PatientOrderStatusNotification extends Notification
{
    use Queueable;

    public Order $order;
    public string $eventType;
    public string $title;
    public string $message;

    public function __construct(Order $order, string $eventType, string $message, string $title = 'Pembaruan Status Pesanan')
    {
        $this->order = $order;
        $this->eventType = $eventType;
        $this->message = $message;
        $this->title = $title;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->eventType,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->eventType ?: $this->order->status,
            'title' => $this->title,
            'message' => $this->message,
            'nurse_name' => $this->order->nurse?->name ?? 'Mitra Perawat',
            'formatted_time' => now()->format('d M Y, H:i') . ' WIB',
        ];
    }
}

