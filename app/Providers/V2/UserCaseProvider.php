<?php

namespace App\Providers\V2;

use App\Services\V2\CommonService;
use App\Services\V2\UserService;
use App\Services\V2\WorkerRecommendationService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UserCaseProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {
        $this->app->singleton(CommonService::class, function(Application $app) {return new CommonService();});
        $this->app->singleton(UserService::class,function(Application $app) {return new UserService();});
        $this->app->singleton(WorkerRecommendationService::class,
            function(Application $app) {return new WorkerRecommendationService();});
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
