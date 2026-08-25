<?php

namespace App\Notifications;

use App\InternalMessage;
use App\Traits\SmtpSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InternalMessageReceived extends Notification
{
    use Queueable, SmtpSettings;

    public function __construct(public InternalMessage $internalMessage)
    {
        $this->setMailConfigs();
    }

    public function via($notifiable): array
    {
        return $notifiable->email ? ['mail'] : [];
    }

    public function toMail($notifiable): MailMessage
    {
        $sender = $this->internalMessage->sender;
        $conversationUrl = route('admin.internal-messages.index', ['recipient' => $sender->id]);

        return (new MailMessage)
            ->subject('New internal ATS message from '.ucwords($sender->name))
            ->greeting('Hello '.ucwords($notifiable->name).',')
            ->line(ucwords($sender->name).' sent you an internal message:')
            ->line('“'.$this->internalMessage->body.'”')
            ->action('Open Conversation', $conversationUrl)
            ->line('Reply securely from the Internal Messages page in the ATS.');
    }
}