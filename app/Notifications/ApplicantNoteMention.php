<?php

namespace App\Notifications;

use App\ApplicantNote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicantNoteMention extends Notification
{
    use Queueable;

    public $note;

    public function __construct(ApplicantNote $note)
    {
        $this->note = $note;
    }

    public function via($notifiable)
    {
        return ['database']; // add 'mail' if you want email too
    }

    public function toDatabase($notifiable)
    {
        return [
            'note_id'          => $this->note->id,
            'note_text'        => $this->note->note_text,
            'mentioned_by'     => auth()->user()->name ?? 'Someone',
            'application_id'   => $this->note->job_application_id,
            'message'          => auth()->user()->name . ' mentioned you in a note.',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('You were mentioned in a note')
            ->line(auth()->user()->name . ' mentioned you in a note:')
            ->line('"' . $this->note->note_text . '"')
            ->action('View Application', url('/admin/job-applications/' . $this->note->job_application_id));
    }
}