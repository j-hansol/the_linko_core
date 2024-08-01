<?php

namespace App\Providers;

use App\Events\AccountCreated;
use App\Events\ConsultingConfirmed;
use App\Events\ConsultingMessageCreated;
use App\Events\VisaIssueTaskAssigned;
use App\Listeners\SendAssignTaskNotificationToAttorney;
use App\Listeners\SendAssignTaskNotificationToWorker;
use App\Listeners\SendConsultingMesssageNotification;
use App\Listeners\SendNotificationConsultingConfirm;
use App\Listeners\SendRequestAcceptAccountMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [SendEmailVerificationNotification::class,],
        AccountCreated::class => [SendRequestAcceptAccountMail::class],
        ConsultingConfirmed::class => [SendNotificationConsultingConfirm::class],
        ConsultingMessageCreated::class => [SendConsultingMesssageNotification::class],
        VisaIssueTaskAssigned::class => [
            SendAssignTaskNotificationToAttorney::class,
            SendAssignTaskNotificationToWorker::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
