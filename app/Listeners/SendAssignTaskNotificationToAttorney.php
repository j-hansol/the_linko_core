<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\VisaIssueTaskAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignTaskNotificationToAttorney
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void {
        if($event->visa instanceof Collection) $visa = $event->visa->first();
        else $visa = $event->visa;

        $attorney = User::findMe($visa->attorney_user_id);
        $attorney?->notify(new VisaIssueTaskAssigned());
    }
}
