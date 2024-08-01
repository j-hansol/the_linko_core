<?php

namespace App\Listeners;

use App\Events\ConsultingConfirmed;
use App\Models\User;
use App\Notifications\ConsultingAttorneyAssigned;
use App\Notifications\ConsultingPermissionConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationConsultingConfirm
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(ConsultingConfirmed $event): void {
        // 여러 비자 정보의 컨설팅 요청을 수락한 경우
        if(is_array($event->visa) && count($event->visa) > 0) {
            $attorney = User::findMe($event->visa[0]->consulting_user_id);
            $cnt = 0;
            foreach ($event->visa as $visa) {
                if($visa->isConsulting()) {
                    $worker = User::findMe($visa->user_id);
                    $worker->notify(new ConsultingAttorneyAssigned());
                    ++$cnt;
                }
            }
            if($cnt > 0) $attorney->notify(new ConsultingPermissionConfirmed());
        }
        // 하나의 비자 정보의 컨설팅 요청을 수랋한 경우
        else {
            $attorney = User::findMe($event->visa->consulting_user_id);
            $worker = User::findMe($event->visa->user_id);
            if($attorney && $worker) {
                $attorney->notify(new ConsultingPermissionConfirmed());
                $worker->notify(new ConsultingAttorneyAssigned());
            }
        }
    }
}
