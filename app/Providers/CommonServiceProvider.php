<?php

namespace App\Providers;

use App\Http\QueryParams\CountryParam;
use App\Http\QueryParams\EvalTargetQueryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Http\QueryParams\UserTypeParam;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {
        $this->app->bind(ListQueryParam::class,
            function(Application $app) {return new ListQueryParam($app);});
        $this->app->bind(CountryParam::class,
            function(Application $app) {return new CountryParam($app);});
        $this->app->bind(UserTypeParam::class,
            function(Application $app) {return new UserTypeParam($app);});
        $this->app->bind(EvalTargetQueryParam::class,
            function(Application $app) {return new EvalTargetQueryParam($app);});
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
