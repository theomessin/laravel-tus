<?php

namespace Theomessin\Tus;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->mapTusRoutes();
        $this->bootConsole();
    }

    /**
     * Define the routes for the package.
     */
    protected function mapTusRoutes()
    {
        $options = [
            'prefix' => 'tus',
            'namespace' => '\Theomessin\Tus\Http\Controllers',
        ];

        Route::group($options, static function ($router) {
            (new RouteRegistrar($router))->all();
        });
    }

    /**
     * Console specific booting.
     */
    protected function bootConsole()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->registerCommands();
        }
    }

    /**
     * Publish the package config.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('tus.php'),
        ], 'config');
    }

    /**
     * Register package Artisan commands.
     */
    protected function registerCommands()
    {
        $this->commands([]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->applyConfig();
    }

    /**
     * Automatically apply the package config
     */
    protected function applyConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'tus');
    }
}
