<?php

namespace App\Listeners;

use App\Events\WorkerActionPointChanged;
use App\Models\AssignedWorker;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyWorkerActionPoint {
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(WorkerActionPointChanged $event): void {
        $assigned_worker = AssignedWorker::findMe($event->history->assigned_worker_id);
        $operator = User::findMe($assigned_worker->manager_operator_user_id);
        $operator?->notify(new WorkerActionPointChanged($event->history));
    }
}
