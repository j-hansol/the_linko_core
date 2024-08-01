<?php

namespace App\Listeners;

use App\Events\ConsultingMessageCreated;
use App\Models\User;
use App\Models\VisaApplication;
use App\Notifications\VisaConsultingMessageRecieved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendConsultingMesssageNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(ConsultingMessageCreated $event): void {
        $author = User::findMe($event->message->user_id);
        $visa = VisaApplication::findMe($event->message->visa_application_id);
        if($visa->user_id == $author->id) {
            if($visa->isConsulting()) $attorney = User::findMe($visa->consulting_user_id);
            elseif($visa->isInIssueProcess()) $attorney = User::findMe($visa->attorney_user_id);
            else $attorney = null;
            if($attorney) $attorney->notify(new VisaConsultingMessageRecieved(true));
        }
        else {
            $worker = User::findMe($visa->user_id);
            $worker->notify(new VisaConsultingMessageRecieved(false));
        }
    }
}
