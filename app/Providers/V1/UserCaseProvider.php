<?php

namespace App\Providers\V1;

use App\Services\V1\CompanyService;
use App\Services\V1\ContractService;
use App\Services\V1\EvaluationService;
use App\Services\V1\ManagerOperatorService;
use App\Services\V1\ManagerPoolService;
use App\Services\V1\UserService;
use App\Services\V1\VisaApplicationService;
use App\Services\V1\WorkerManagementService;
use App\Services\V1\WorkerMonitoringService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UserCaseProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {
        $this->app->singleton(UserService::class,
            function(Application $app) {return new UserService();});
        $this->app->singleton(VisaApplicationService::class,
            function(Application $app) {return new VisaApplicationService();});
        $this->app->singleton(ContractService::class,
            function(Application $app) {return new ContractService();});
        $this->app->singleton(WorkerManagementService::class,
            function(Application $app) {return new WorkerManagementService();});
        $this->app->singleton(ManagerOperatorService::class,
            function(Application $app) {return new ManagerOperatorService();});
        $this->app->singleton(ManagerPoolService::class,
            function(Application $app) {return new ManagerPoolService();});
        $this->app->singleton(EvaluationService::class,
            function(Application $app) {return new EvaluationService();});
        $this->app->singleton(CompanyService::class,
            function(Application $app) {return new CompanyService();});
        $this->app->singleton(WorkerMonitoringService::class,
            function(Application $app) {return new WorkerMonitoringService();});
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
