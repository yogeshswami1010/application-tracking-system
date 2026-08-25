<?php

namespace App\Notifications;

use App\InternalMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InternalMessageReceived extends Notification
{
    use Queueable;

    public function __construct(public InternalMessage $internalMessage)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $sender = $this->internalMessage->sender;

        return [
            'message_id' => $this->internalMessage->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'message_text' => $this->internalMessage->body,
            'message' => ucwords($sender->name).' sent you an internal message.',
        ];
    }
}