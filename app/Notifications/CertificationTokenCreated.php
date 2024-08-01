<?php

namespace App\Notifications;

use App\Models\CertificationToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificationTokenCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct( public User $user, public CertificationToken $token) {}

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
        $language_code =  match ($this->user->getCountryCode()) {
            'KR' => 'ko',
            default => 'en'
        };
        $template_path = match ($this->user->getCountryCode()) {
            'KR' => 'ko.notification.certification_token_created',
            default => 'en.notification.certification_token_created'
        };

        return (new MailMessage)->subject(__('certification_token.token_created', [], $language_code))->view(
            "${language_code}.notification.certification_token_created", [
                'function_name' => __('certification_token.function.' . $this->token->target_function, [], $language_code),
                'token' => $this->token->token
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array {return [];}
}
