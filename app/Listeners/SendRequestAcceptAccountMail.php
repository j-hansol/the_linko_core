<?php

namespace App\Listeners;

use App\Events\AccountCreated;
use App\Lib\MemberType;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestAcceptAccount;

class SendRequestAcceptAccountMail
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(AccountCreated $event): void {
        $operator_ids = UserType::getUserIdsByType(MemberType::TYPE_OPERATOR);
        if($operator_ids->isNotEmpty()) {
            $operators = User::find($operator_ids->pluck('user_id')->toArray());
            if($operators->isNotEmpty()) {
                $emails = $operators->pluck('email')->toArray();
                Mail::to($emails)->send(new RequestAcceptAccount($event->account));
            }
        }
    }
}
