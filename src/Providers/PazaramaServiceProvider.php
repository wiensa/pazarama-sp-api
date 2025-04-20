<?php

namespace PazaramaApi\PazaramaSpApi\Providers;

use Illuminate\Support\ServiceProvider;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\ApiService;
use PazaramaApi\PazaramaSpApi\Services\BrandService;
use PazaramaApi\PazaramaSpApi\Services\BulkOperationService;
use PazaramaApi\PazaramaSpApi\Services\CategoryService;
use PazaramaApi\PazaramaSpApi\Services\OrderService;
use PazaramaApi\PazaramaSpApi\Services\ProductService;
use PazaramaApi\PazaramaSpApi\Services\ReturnService;
use PazaramaApi\PazaramaSpApi\Services\ShippingService;

class PazaramaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/pazarama-api.php' => config_path('pazarama-api.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/pazarama-api.php', 'pazarama-api');

        // API servisini kaydet
        $this->app->singleton('pazarama.api', function () {
            return new ApiService(
                config('pazarama-api.client_id'),
                config('pazarama-api.client_secret'),
                config('pazarama-api.base_url', 'https://isortagimapi.pazarama.com'),
                config('pazarama-api.auth_url', 'https://isortagimgiris.pazarama.com/connect/token'),
                config('pazarama-api.timeout', 30),
                config('pazarama-api.debug', false)
            );
        });

        // PazaramaSpApi'yi kaydet
        $this->app->singleton('pazarama', function ($app) {
            return new PazaramaSpApi([
                'base_url' => config('pazarama-api.base_url', 'https://isortagimapi.pazarama.com'),
                'auth_url' => config('pazarama-api.auth_url', 'https://isortagimgiris.pazarama.com/connect/token'),
                'client_id' => config('pazarama-api.client_id'),
                'client_secret' => config('pazarama-api.client_secret'),
                'timeout' => config('pazarama-api.timeout', 30),
            ]);
        });

        // Diğer servisleri kaydet
        $this->app->singleton('pazarama.product', function ($app) {
            return new ProductService($app->make('pazarama'));
        });

        $this->app->singleton('pazarama.order', function ($app) {
            return new OrderService($app->make('pazarama'));
        });

        $this->app->singleton('pazarama.category', function ($app) {
            return new CategoryService($app->make('pazarama'));
        });

        $this->app->singleton('pazarama.shipping', function ($app) {
            return new ShippingService($app->make('pazarama'));
        });

        $this->app->singleton('pazarama.brand', function ($app) {
            return new BrandService($app->make('pazarama'));
        });

        $this->app->singleton('pazarama.bulk', function ($app) {
            return new BulkOperationService($app->make('pazarama'));
        });

        $this->app->singleton('pazarama.return', function ($app) {
            return new ReturnService($app->make('pazarama'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            'pazarama',
            'pazarama.api',
            'pazarama.product',
            'pazarama.order',
            'pazarama.category',
            'pazarama.shipping',
            'pazarama.brand',
            'pazarama.bulk',
            'pazarama.return',
        ];
    }
} 