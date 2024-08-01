<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisaConsultingMessageRecieved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public bool $fromWorker = false) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage {
        $subject = $this->fromWorker ? '비자발급관련 메시지가 수신되었습니다.' : 'A message related to visa issuance has been received.';
        return (new MailMessage)->subject($subject)
            ->view($this->fromWorker ? 'ko.notification.message_received': 'en.notification.message_received');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array { return [];}
}
