<?php

namespace PazaramaApi\PazaramaSpApi\Providers;

use Illuminate\Support\ServiceProvider;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class PazaramaSpApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfig();
    }

    public function register(): void
    {
        $this->registerConfig();
        $this->registerPazaramaSpApi();
    }

    // Boot methods :
    private function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__, 2).'/config/pazarama-sp-api.php' => config_path('pazarama-sp-api.php'),
            ], 'config');
        }
    }

    // Register methods :
    private function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/pazarama-sp-api.php', 'pazarama-sp-api');
    }

    private function registerPazaramaSpApi(): void
    {
        $this->app->singleton('pazarama-sp-api', function ($app) {
            return new PazaramaSpApi(config('pazarama-sp-api'));
        });
    }
}
