<?php

namespace PazaramaApi\PazaramaSpApi\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PazaramaApi\PazaramaSpApi\Providers\PazaramaServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            PazaramaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Test için gerekli olan ortam değişkenleri
        $app['config']->set('pazarama-api.client_id', 'test_client_id');
        $app['config']->set('pazarama-api.client_secret', 'test_client_secret');
        $app['config']->set('pazarama-api.base_url', 'https://isortagimapi.pazarama.com');
        $app['config']->set('pazarama-api.auth_url', 'https://isortagimgiris.pazarama.com/connect/token');
        $app['config']->set('pazarama-api.timeout', 30);
        $app['config']->set('pazarama-api.debug', false);
        $app['config']->set('pazarama-api.retry_attempts', 3);
        $app['config']->set('pazarama-api.retry_delay', 1000);
    }
} 