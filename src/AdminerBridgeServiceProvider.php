<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ServiceProvider;

class AdminerBridgeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/adminer-bridge.php', 'adminer-bridge');

        $this->app->bind(SessionManager::class, fn (Application $app) => $app->make('session'));

        $this->app->singleton(AdminerBridge::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/adminer-bridge.php');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/adminer-bridge.php' => config_path('adminer-bridge.php'),
        ], ['adminer-bridge', 'adminer-bridge-config']);
    }
}
