<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemActivityNotification extends Notification
{
    use Queueable;

    public string $type;
    public string $title;
    public string $message;
    public array $extra;

    public function __construct(string $type, string $title, string $message, array $extra = [])
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->extra = $extra;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'formatted_time' => now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
        ], $this->extra);
    }
}
