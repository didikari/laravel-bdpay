<?php

namespace BDPay\LaravelBDPay;

use Illuminate\Support\ServiceProvider;
use BDPay\LaravelBDPay\Services\BDPayClient;
use BDPay\LaravelBDPay\Services\BDPayService;
use BDPay\LaravelBDPay\Services\FundAcceptanceService;
use BDPay\LaravelBDPay\Services\FundDisbursementService;
use BDPay\LaravelBDPay\Console\Commands\PublishBDPayAssets;

class BDPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bdpay.php',
            'bdpay'
        );

        $this->app->singleton(BDPayClient::class, function ($app) {
            return new BDPayClient($app['config']['bdpay']);
        });

        $this->app->singleton(FundAcceptanceService::class, function ($app) {
            return new FundAcceptanceService($app[BDPayClient::class]);
        });

        $this->app->singleton(FundDisbursementService::class, function ($app) {
            return new FundDisbursementService($app[BDPayClient::class]);
        });

        $this->app->singleton(BDPayService::class, function ($app) {
            return new BDPayService(
                $app[BDPayClient::class],
                $app[FundAcceptanceService::class],
                $app[FundDisbursementService::class]
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bdpay.php' => config_path('bdpay.php'),
            ], 'bdpay-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'bdpay-migrations');

            $this->publishes([
                __DIR__ . '/../routes/web.php' => base_path('routes/bdpay-webhooks.php'),
            ], 'bdpay-routes');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Load webhook routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishBDPayAssets::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            BDPayClient::class,
            BDPayService::class,
            FundAcceptanceService::class,
            FundDisbursementService::class,
        ];
    }
}
