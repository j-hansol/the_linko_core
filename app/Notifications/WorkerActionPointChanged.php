<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WorkerActionPointHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkerActionPointChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly WorkerActionPointHistory $history) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage {
        $subject = '비자발급 업무가 배정되었습니다.';
        $worker = User::findMe($this->history->worker_user_id);
        $company = User::findMe($this->history->company_user_id);
        return (new MailMessage)->subject($subject)
            ->view( 'ko.notification.worker_action_point_changed', ['worker' => $worker, 'company' => $worker]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array {
        return [];
    }
}
