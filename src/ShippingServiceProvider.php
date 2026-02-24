<?php

namespace Mohamedali\LaravelShipping;

use Illuminate\Support\ServiceProvider;
use Mohamedali\LaravelShipping\Services\ShippingManager;
use Mohamedali\LaravelShipping\Console\Commands\MakeShippingDriver;
use Mohamedali\LaravelShipping\Console\Commands\MakeShippingPayload;
use Mohamedali\LaravelShipping\Console\Commands\PublishShippingConfig;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shipping.php', 'shipping');

        $this->app->singleton(ShippingManager::class, function ($app) {
            return new ShippingManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/shipping.php' => config_path('shipping.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/migrations' => database_path('migrations'),
            ], 'migrations');

            $this->commands([
                MakeShippingDriver::class,
                MakeShippingPayload::class,
                PublishShippingConfig::class,
            ]);
        }
    }
}
