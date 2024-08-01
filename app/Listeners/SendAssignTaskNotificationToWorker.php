<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\VisaApplication;
use App\Notifications\OwnedVisaIssueTaskAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignTaskNotificationToWorker
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void {
        if($event->visa instanceof Collection)
            foreach($event->visa as $visa) $this->notify($visa);
        else $this->notify($event->visa);
    }

    private function notify(VisaApplication $visa) : void {
        $worker = User::findMe($visa->user_id);
        $worker?->notify(new OwnedVisaIssueTaskAssigned());
    }
}
